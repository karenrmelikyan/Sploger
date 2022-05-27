<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CacheStatus;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\Eloquent\SettingsRepository;
use App\Repositories\KeywordRepositoryInterface;
use App\Services\MarkovChain;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use JsonException;

use Psr\Log\LoggerInterface;

use RuntimeException;

use Throwable;

use function count;
use function json_decode;
use function json_encode;
use function preg_replace;

final class GenerateMarkovTable implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use Batchable;

    public function __construct(private readonly string $keyword, private readonly string $languageCode)
    {
        $this->onQueue('markov');
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(
        ConnectionInterface $db,
        KeywordRepositoryInterface $keywordRepository,
        ArticleRepositoryInterface $articleRepository,
        SettingsRepository $settings,
        LoggerInterface $logger,
        Dispatcher $dispatcher
    ): void
    {
        if ($this->batch() !== null && $this->batch()->canceled()) {
            return;
        }

        $keyword = $keywordRepository->findByName($this->keyword, $this->languageCode);
        if ($keyword === null) {
            $logger->debug('Keyword not found for the job');
            throw new RuntimeException('Keyword not found for the job');
        }

        if ($keyword->article_cache_status === CacheStatus::PENDING) {
            $logger->debug('Article scraping is pending, releasing for 15 minutes.');
            $this->release(now()->addMinutes(15));
            return;
        }

        $matrix = $db->table('markov_matrix')->where([
            'keyword_id' => $keyword->id,
        ])->value('matrix');

        if ($matrix !== null) {
            if (json_decode($matrix, true,512, JSON_THROW_ON_ERROR) !== []) {
                // We don't need to generate matrix, as we already have it
                $logger->debug('Markov is already present.');
                $keyword->markov_cache_status = CacheStatus::CACHED;
                $keyword->save();
                return;
            }

            $logger->debug('Matrix is empty, deleting.');
            // Delete markov if it is present and empty.
            $db->table('markov_matrix')->where([
                'keyword_id' => $keyword->id,
            ])->delete();
        }

        $articles = $articleRepository->findForKeyword($keyword->id);

        // Articles are not present, we need to scrape them first
        if ($articles->isEmpty()) {
            $job = (new CrawlBingUrlsForKeyword($keyword->name, $keyword->language_code))
                ->chain([new self($this->keyword, $this->languageCode)]);
            $keyword->article_cache_status = CacheStatus::PENDING;
            $db->beginTransaction();
            try {
                $dispatcher->dispatch($job);
                $keyword->save();
                $this->delete();

                $db->commit();
                $logger->debug('Chaining article scraping job first.');
                return;
            } catch (Throwable $e) {
                $db->rollBack();
                throw $e;
            }
        }

        $wordsBlacklist = json_decode($settings->findByName('words_blacklist')->value ?? '[]', false, 512, JSON_THROW_ON_ERROR);
        $text = $articles->implode('content', ' ');
        $text = preg_replace(array_map(static fn (string $word) => "/\b$word\b/i", $wordsBlacklist), '', $text);

        $matrix = MarkovChain::generateMarkovMatrix($text);
        if ($matrix === []) {
            $logger->debug('Generated markov is empty. Chaining article scraping job first.', [
                'articles' => $articles->toArray(),
            ]);
            // Got empty matrix, remove articles to rescrape them.
            $job = (new CrawlBingUrlsForKeyword($keyword->name, $keyword->language_code))
                ->chain([new self($this->keyword, $this->languageCode)]);
            $keyword->article_cache_status = CacheStatus::PENDING;
            $db->beginTransaction();
            try {
                $dispatcher->dispatch($job);
                $keyword->save();
                $this->delete();
                $db->table('articles')->where([
                    'keyword_id' => $keyword->id
                ])->delete();

                $db->commit();
                $logger->debug('Chaining article scraping job first.');
                return;
            } catch (Throwable $e) {
                $db->rollBack();
                throw $e;
            }
        }

        $matrix_quality = MarkovChain::tokenize($text);

        $db->table('markov_matrix')->insert([
            'keyword_id' => $keyword->id,
            'matrix' => json_encode($matrix, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'tokens' => count($matrix_quality['tokens']),
            'distinct_tokens' => count($matrix_quality['distinctTokens']),
        ]);

        $keyword->markov_cache_status = CacheStatus::CACHED;
        $keyword->save();
        $db->statement('ANALYZE TABLE markov_matrix');
    }

    public function displayName(): string
    {
        return 'Generating Markov Chain for "' . $this->keyword . '" (' . $this->languageCode . ')';
    }
}
