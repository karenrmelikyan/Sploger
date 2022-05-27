<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Requests\Project\StoreRequest;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function create(string $name, string $language, int $keywordSetId): Project;

    public function createFromRequest(StoreRequest $request): Project;

    public function updateFromRequest(StoreRequest $request): Project;

    public function update(Project $project): bool;

    public function delete(int $id): void;

    public function findById(int $id, bool $withSplogs = false): ?Project;

    public function findAllPaginated(int $perPage = 50, bool $withKeywordSet = false, bool $withSplogsCount = false): LengthAwarePaginator;
}
