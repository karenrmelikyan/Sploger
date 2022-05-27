<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CacheStatus;
use App\Models\Keyword;
use App\Repositories\Eloquent\SettingsRepository;
use App\Services\BingScraper;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use JsonException;
use Pdp\Domain;
use Pdp\TopLevelDomains;

use Throwable;

use function array_filter;
use function array_map;
use function count;
use function in_array;
use function json_decode;
use function parse_url;

final class CrawlBingUrlsForKeyword implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private string $keyword, private string $languageCode)
    {
        $this->onQueue('crawler');
    }

    /**
     * @param BingScraper $scraper
     * @param Dispatcher $dispatcher
     * @param FilesystemManager $fm
     * @param SettingsRepository $settings
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(BingScraper $scraper, Dispatcher $dispatcher, FilesystemManager $fm, SettingsRepository $settings): void
    {
        $numberOfResults = 50;
        $page = 1;
        $urls = [];

        $blacklist = json_decode($settings->findByName('url_blacklist')->value ?? '[]', false, 512, JSON_THROW_ON_ERROR);

        $topLevelDomains = TopLevelDomains::fromPath($fm->disk()->path('tlds-alpha-by-domain.txt'));
        $isBlacklisted = static function(string $url) use ($topLevelDomains, $blacklist) {
            $host = parse_url($url, PHP_URL_HOST);
            $domain = Domain::fromIDNA2008($host);

            return in_array($topLevelDomains->resolve($domain)->registrableDomain(), $blacklist, true);
        };

        while (count($urls) < $numberOfResults) {
            $currentNumberOfResults = count($urls);
            $urls = [
                ...$urls,
                ...array_filter(
                    array_map(
                        static fn(array $result) => $result['url'],
                        $scraper->getResults($this->keyword, $this->languageCode, $page++)
                    ),
                    static function (string $url) use ($urls, $isBlacklisted) {
                        return !in_array($url, $urls, true) && !$isBlacklisted($url);
                    }
                ),
            ];
            // If actual search results were less than expected, break infinite loop
            // as further BING pages return same first page
            if (count($urls) === $currentNumberOfResults) {
                break;
            }
        }

        $scrapeArticles = array_map(
            fn (string $url) => new ScrapeArticle($url, $this->keyword, $this->languageCode),
            $urls
        );

        $keyword = $this->keyword;
        $languageCode = $this->languageCode;

        // Chain markov generation if needed (fix to correctly chain after batch)
        $chainedJob = null;
        if (count($this->chained) === 1) {
            // We have chained job
            $chainedJob = unserialize($this->chained[0], ['allowed_classes' => [GenerateMarkovTable::class]]);
            $this->chained = [];
        }

        // Schedule tasks to post articles
        $dispatcher->batch($scrapeArticles)
            ->name('Scrape articles for ' . $keyword)
            ->onQueue('crawler')
            ->allowFailures()
            ->finally(function () use ($keyword, $languageCode, $chainedJob) {
                Keyword::where(['name' => $keyword, 'language_code' => $languageCode])->update(['article_cache_status' => CacheStatus::CACHED]);
                if ($chainedJob !== null) {
                    Bus::dispatch($chainedJob);
                }
                //else {
                    //Bus::dispatch(new GenerateMarkovTable($keyword, $languageCode));
                //}
            })->dispatch();
    }

    public function displayName(): string
    {
        return 'Scraping BING urls for "' . $this->keyword . '" (' . $this->languageCode . ')';
    }
}


