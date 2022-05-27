<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\KeywordRepositoryInterface;
use App\Services\ArticleScraper;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

use function count;

final class ScrapeArticle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use Batchable;
    use SerializesModels;

    public function __construct(private string $url, private string $keyword, private string $languageCode)
    {
        $this->onQueue('crawler');
    }

    public function handle(ArticleScraper $scraper, KeywordRepositoryInterface $keywordRepository, ArticleRepositoryInterface $repository, Connection $db): void
    {
        if ($this->batch() !== null && $this->batch()->canceled()) {
            return;
        }
        $truncatedUrl = Str::limit($this->url, 255, '');

        $articles = $repository->findByUrl($truncatedUrl);
        $keyword = $keywordRepository->findByName($this->keyword, $this->languageCode);
        if ($keyword === null) {
            // Impossible
            return;
        }

        if (count($articles) > 0) {
            $filtered = $articles->filter(function (Article $article) use ($keyword) {
                return $article->keyword_id === $keyword->id;
            });

            if ($filtered->count() === 0) {
                $repository->create($keyword->id, $truncatedUrl, $articles[0]->content);
            }

            return;
        }

        $content = $scraper->extract($this->url);
        $repository->create($keyword->id, $truncatedUrl, $content);
        $db->statement('ANALYZE TABLE articles');
    }

    public function displayName(): string
    {
        return 'Scraping article from ' . $this->url;
    }
}
