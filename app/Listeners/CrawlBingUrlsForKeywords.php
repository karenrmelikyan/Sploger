<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\CacheStatus;
use App\Events\ProjectCreated;
use App\Jobs\CrawlBingUrlsForKeyword;
use App\Jobs\GenerateMarkovTable;
use Doctrine\Common\Cache\Cache;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Connection;
use Throwable;

final class CrawlBingUrlsForKeywords
{
    public bool $afterCommit = true;

    public function __construct(private Dispatcher $dispatcher)
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function handle(ProjectCreated $event): void
    {
        $event->project->load('keywordSet.keywords');
        $keywords = $event->project->keywordSet->keywords;
        $crawlKeywordsAndGenerateMarkov = [];

        foreach ($keywords as $keyword) {
            if ($keyword->markov_cache_status === CacheStatus::CACHED) {
                continue;
            }

            if ($keyword->article_cache_status === CacheStatus::CACHED && $keyword->markov_cache_status !== CacheStatus::EMPTY) {
                // TODO: refactor to bulk update
                $keyword->markov_cache_status = CacheStatus::PENDING;
                $keyword->save();
                $crawlKeywordsAndGenerateMarkov[] = new GenerateMarkovTable($keyword->name, $keyword->language_code);
            } elseif ($keyword->article_cache_status === CacheStatus::PENDING && $keyword->markov_cache_status !== CacheStatus::PENDING) {
                return;
            } else {
                // TODO: refactor to bulk update
                $keyword->article_cache_status = CacheStatus::PENDING;
                $keyword->markov_cache_status = CacheStatus::PENDING;
                $keyword->save();
                $crawlKeywordsAndGenerateMarkov[] = [
                    new CrawlBingUrlsForKeyword($keyword->name, $keyword->language_code),
                    new GenerateMarkovTable($keyword->name, $keyword->language_code)
                ];
            }
        }

        $this->dispatcher
            ->batch($crawlKeywordsAndGenerateMarkov)
            ->name('Crawl Bing for urls for project: ' . $event->project->id)
            ->onQueue('crawler')
            ->dispatch();
    }
}
