<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\KeywordSet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KeywordSetRepositoryInterface
{
    public function create(string $name, string $keywords): KeywordSet;

    public function update(KeywordSet $set): bool;

    public function delete(int $id): void;

    public function findById(int $id, bool $withKeywords = false): ?KeywordSet;

    public function findAllPaginated(int $perPage = 50): LengthAwarePaginator;

    /**
     * @return array|Collection
     */
    public function findAll(): array|Collection;
}
