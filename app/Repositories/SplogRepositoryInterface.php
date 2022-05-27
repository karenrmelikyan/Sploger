<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Splog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SplogRepositoryInterface
{
    public function findAllPaginated(int $perPage = 20, bool $withProject = false): LengthAwarePaginator;

    public function findByDomain(string $domain): ?Splog;

    public function delete(int $id): void;
}
