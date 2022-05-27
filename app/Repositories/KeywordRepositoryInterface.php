<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Keyword;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface KeywordRepositoryInterface
{
    /**
     * @param array $keywords
     * @param bool $localTransaction
     * @return Keyword[]
     */
    public function bulkCreate(array $keywords, string $language_code, bool $localTransaction = true): array;

    public function findByName(string $name, string $languageCode): ?Keyword;

    public function delete(int $id): void;

    public function findBySet(int $setId, int $perPage = 50, bool $withArticlesCount = false): LengthAwarePaginator;

    public function findById(int $id): ?Keyword;
}
