<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CacheStatus;
use App\Jobs\Middleware\WithoutOverlapping;
use App\Jobs\Middleware\RateLimited;
use App\Models\Keyword;
use App\Repositories\SplogRepositoryInterface;
use App\Services\BingScraper;
use App\Services\MarkovChain;
use App\Services\WordpressService;
use DateTime;
use ErrorException;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function array_map;
use function array_rand;
use function count;
use function json_decode;
use function md5;
use function now;
use function random_int;

final class CreateSplogPost implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const LIMITER_NAME = 'server-requests';

    private ConnectionInterface $db;
    private Dispatcher $dispatcher;
    private BingScraper $bingScraper;
    private LoggerInterface $logger;
    private WordpressService $wp;
    private array $markovMatrix = [];

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    public function __construct(
        public readonly string $serverIp,
        private readonly string $host,
        private readonly Keyword $keyword,
        private readonly string $languageCode,
        private readonly array $sections,
        private readonly array $wordsPerSection,
        private readonly ?int $keywordDensity,
        private readonly ?int $schedule_interval,
        private readonly ?int $schedule_variance
    ) {
        $this->onQueue('splog-posts');
    }

    /**
     * Determine the time at which the job should timeout.
     * @noinspection PhpUnused
     */
    public function retryUntil(): DateTime
    {
        return now()->addDays(3);
    }

    /**
     * Get the middleware the job should pass through.
     * @throws Exception
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->host))
                ->releaseAfter($this->schedule_interval !== null ? ($this->schedule_interval * 60 + random_int(-15, 15)) : random_int(5, 30))
                ->expireAfter(60),
            new RateLimited(self::LIMITER_NAME),
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Throwable
     */
    public function handle(
        ConnectionInterface $db,
        WordpressService $wp,
        BingScraper $bingScraper,
        LoggerInterface $logger,
        SplogRepositoryInterface $splogRepository,
        Dispatcher $dispatcher,
    ): void
    {
        $this->db = $db;
        $this->dispatcher = $dispatcher;
        $this->bingScraper = $bingScraper;
        $this->logger = $logger;
        $this->wp = $wp;

        if (!$this->shouldContinue()) {
            return;
        }

        $splog = $splogRepository->findByDomain($this->host);
        if ($splog === null) {
            // Splog is already deleted. We don't need to continue the job.
            $logger->debug('Splog is already removed. Cancelling the job.');
            $this->delete();
            return;
        }

        // Check if there is schedule for the posts
        if ($splog->next_post_at->gt(now())) {
            $this->release($splog->next_post_at->diffInSeconds(now()));
            $logger->debug('Scheduled for: ' . $splog->next_post_at->diffInSeconds(now()));
            return;
        }

        $this->createSplogPost();
        if ($this->schedule_interval !== null) {
            $nextPostDelay = $this->schedule_interval + ($this->schedule_variance === null ? 0 : random_int(
                    -1 * $this->schedule_variance,
                    $this->schedule_variance
                ));
            $splog->next_post_at = $splog->next_post_at->addMinutes($nextPostDelay);
            $logger->debug("Next post is scheduled after $nextPostDelay minutes.");
            $splog->save();
        }

        $logger->debug("Posted on $this->host created for keyword: " . $this->keyword->name);
    }

    /**
     * Handle a job failure.
     * @throws BindingResolutionException
     */
    public function failed(Throwable $exception): void
    {
        /** @var LoggerInterface $logger */
        $logger = Container::getInstance()->make(LoggerInterface::class);
        $logger->debug('Job failed: ' . $exception->getMessage());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    private function shouldContinue(): bool
    {
        $this->logger->debug('Articles cache status: ' . $this->keyword->article_cache_status->status());
        $this->logger->debug('Markov cache status: ' . $this->keyword->markov_cache_status->status());

        // If markov generation is in progress, release
        if ($this->keyword->markov_cache_status === CacheStatus::PENDING) {
            $this->logger->debug('Markov generation in progress, releasing ' . $this->keyword->name);
            $this->release(now()->addMinutes(15));
            $this->decrementLimiterAttempts();

            return false;
        }

        // If markov is not there, dispatch generation jobs first
        if ($this->keyword->markov_cache_status === CacheStatus::EMPTY) {

            if ($this->keyword->article_cache_status === CacheStatus::PENDING) {
                $this->release(now()->addMinutes(15));
                $this->decrementLimiterAttempts();
                $this->logger->debug('Articles are being scraped, releasing ' . $this->keyword->name);
                return false;
            }

            if ($this->keyword->article_cache_status === CacheStatus::EMPTY) {
                $this->queueExtraJobs(true, true);
                return false;
            }

            $this->queueExtraJobs(true);
            return false;
        }

        $matrix = $this->db->table('markov_matrix')->where([
            'keyword_id' => $this->keyword->id,
        ])->value('matrix');

        if ($matrix === null) {
            $this->logger->debug('Markov is null for ' . $this->keyword->name);
            if ($this->keyword->article_cache_status === CacheStatus::EMPTY) {
                $this->queueExtraJobs(true, true);
                return false;
            }

            $this->queueExtraJobs(true);
            return false;
        }

        $this->markovMatrix = json_decode($matrix, true,512, JSON_THROW_ON_ERROR);

        if (count($this->markovMatrix) === 0) {
            $this->logger->debug('Markov is empty for ' . $this->keyword->name);
            if ($this->keyword->article_cache_status === CacheStatus::EMPTY) {
                $this->queueExtraJobs(true, true);
                return false;
            }

            $this->queueExtraJobs(true);
            return false;
        }

        return true;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    private function queueExtraJobs(bool $includeMarkovGeneration = false, bool $includeArticleScraping = false): void
    {
        $getJobsQueue = function() use ($includeMarkovGeneration, $includeArticleScraping): array {
            $jobs = [];

            if ($includeArticleScraping) {
                $jobs[] = new CrawlBingUrlsForKeyword($this->keyword->name, $this->keyword->language_code);
                $this->keyword->article_cache_status = CacheStatus::PENDING;
            }

            if ($includeMarkovGeneration) {
                $jobs[] = new GenerateMarkovTable($this->keyword->name, $this->keyword->language_code);
                $this->keyword->markov_cache_status = CacheStatus::PENDING;
            }

            $jobs[] = new self(
                $this->serverIp,
                $this->host,
                $this->keyword,
                $this->languageCode,
                $this->sections,
                $this->wordsPerSection,
                $this->keywordDensity,
                $this->schedule_interval,
                $this->schedule_variance,
            );

            return $jobs;
        };

        $this->db->beginTransaction();
        try {
            $this->dispatcher->chain($getJobsQueue())->dispatch();
            $this->keyword->save();
            $this->delete();

            $this->db->commit();
            $this->logger->debug('Added markov job to generate it first.');
            $this->decrementLimiterAttempts();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function decrementLimiterAttempts(): void
    {
        /** @var \App\Cache\RateLimiter $limiter */
        $limiter = Container::getInstance()->get(RateLimiter::class);
        $closure = $limiter->limiter('server-requests');
        /** @var Limit $limit */
        $limit = $closure($this);
        $key = md5('server-requests'.$limit->key);
        $hits = $limiter->reverse($key);

        $this->logger->debug('RateLimiter hits decreased to: ' . $hits);
    }

    public function displayName(): string
    {
        $keyword = $this->keyword->name;
        return "Posting \"$keyword\" keyword article to \"$this->host\"";
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws Exception
     */
    private function createSplogPost(): void
    {
        $this->logger->debug('Authorizing on the splog.');
        try {
            $this->wp->authorize($this->host, 'webmaster', 'thisISapssword!');
        } catch (BadResponseException $e) {
            if ($e->getCode() === 404) { // DNS for the domain was configured incorrectly probably
                $this->fail($e);
                return;
            }

            throw $e;
        }

        // Get recent posts for internal linking
        $this->logger->debug('Getting recent posts.');
        $recentPosts = array_map(
            static fn(array $post) => '<a href="' . $post['link'] . '">' . $post['title']['rendered'] . '</a>',
            $this->wp->listPosts('rand', 3)
        );

        $this->logger->debug('Generating article with Markov chains.');
        $article = MarkovChain::generateMarkovSections($this->sections,
            $this->wordsPerSection,
            $this->markovMatrix,
            $this->keyword->name,
            $this->keywordDensity,
            $recentPosts
        );
        $image = $this->createPostImage();
        if ($image !== null) {
            $article = $image . $article;
        }

        try {
            $this->logger->debug('Creating post for the keyword.');
            $this->wp->createPost(Str::title($this->keyword->name), Str::slug($this->keyword->name), $article);
            //$this->logger->debug('Post created.', $post);
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();
            $this->logger->debug("Posting failed due to $message.");
        }
    }

    /**
     * @throws JsonException
     */
    private function createPostImage(): ?string
    {
        $imageUrl = $this->getRandomImageUrl();
        if ($imageUrl === null) {
            return null;
        }

        try {
            $this->logger->debug('Creating media item for the post.');
            $media = $this->wp->createMediaItem($imageUrl, Str::title($this->keyword->name));
            $imageDetails = $media['media_details']['sizes']['full'];
            return '<a href="' . $media['link'] . '"><img src="' . $imageDetails['source_url'] . '" alt="' . $media['alt_text'] . '" width="' . $imageDetails['width'] . '" height="' . $imageDetails['width'] . '" class="alignnone size-full wp-image-' . $media['id'] . '" /></a>';
        } catch (ClientExceptionInterface | ErrorException $e) {
            $this->logger->debug('Image skipped, reason: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @throws JsonException
     */
    private function getRandomImageUrl(): ?string
    {
        $imageUrls = $this->bingScraper->getImageResults($this->keyword->name, $this->languageCode);

        if (count($imageUrls) === 0) {
            return null;
        }

        return $imageUrls[array_rand($imageUrls)];
    }
}
