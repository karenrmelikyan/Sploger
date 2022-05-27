<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Splog;
use App\Repositories\SplogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SplogRepository implements SplogRepositoryInterface
{
    public function __construct(private Splog $model)
    {
    }

    public function findAllPaginated(int $perPage = 20, bool $withProject = false): LengthAwarePaginator
    {
       return $this->model
            ->when($withProject, static fn (Builder $query) => $query->with('project'))
            ->sortable()
            ->filterable()
            ->paginate($perPage);
    }

    public function findByDomain(string $domain): ?Splog
    {
        return $this->model->firstWhere(['domain' => $domain]);
    }

    public function delete(int $id): void
    {
        $model = $this->findById($id);
        if ($model === null) {
            throw new ModelNotFoundException('Modes doens\'t exist.');
        }
        $model->delete();
    }

    public function findById(int $id): ?Splog
    {
        /** @var Splog|null $model */
        $model = $this->model->find($id);

        return $model;
    }
}
