<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
    public function create(int $keywordId, ?string $url = null, ?string $content = null): Article;

    public function findByUrl(string $url): array|Collection;

    /**
     * @param int $keywordId
     * @param string $languageCode
     * @return Article[]|Collection
     */
    public function findForKeyword(int $keywordId): array|Collection;
}
