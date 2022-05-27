<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Http\Requests\Project\StoreRequest;
use App\Models\Project;
use App\Repositories\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

final class ProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(private Project $model, private ConnectionInterface $db)
    {
    }

    public function create(string $name, string $language, int $keywordSetId): Project
    {
        $model = $this->model->newInstance();
        $model->name = $name;
        $model->save();

        return $model;
    }

    /**
     * @param StoreRequest $request
     * @return Project
     * @throws Throwable
     */
    public function updateFromRequest(StoreRequest $request): Project
    {
        $data = $request->validated();
        $model = $this->findById((int) $data['id']);
        if ($model === null) {
            throw new ModelNotFoundException('Model not found.');
        }

        $this->db->transaction(function () use ($data, $model) {
            $model->fill($data);
            $model->save();

            if (isset($data['splogs'])) {
                $model->splogs()->createMany($data['splogs']);
            }
        });

        return $model;
    }

    /**
     * @throws Throwable
     */
    public function createFromRequest(StoreRequest $request): Project {
        $data = $request->validated();
        $model = $this->model->newInstance($data);

        $this->db->transaction(function () use ($data, $model) {
            $model->fill($data);
            $model->save();

            $model->splogs()->createMany($data['splogs']);
        });

        return $model;
    }

    public function update(Project $project): bool
    {
        return $project->save();
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $model = $this->findById( $id);
        if ($model === null) {
            throw new ModelNotFoundException('Modes doens\'t exist.');
        }
        $model->delete();
    }

    public function findById(int $id, bool $withSplogs = false): ?Project
    {
        /** @var Project|null $model */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $model = $this->model->when($withSplogs, static function (Builder $query) {
            return $query->with('splogs');
        })->find($id);

        return $model;
    }

    public function findAllPaginated(int $perPage = 50, bool $withKeywordSet = false, bool $withSplogsCount = false): LengthAwarePaginator
    {
        return $this->model
            ->when($withKeywordSet, static function (Builder $query) {
                return $query->with(['keywordSet' => function (BelongsTo $query) {
                    $query->where('id', '>', 2);
                }]);
            })
            ->when($withSplogsCount, static function (Builder $query) {
                return $query->withCount('splogs');
            })
            ->sortable()
            ->filterable()
            ->paginate($perPage);
    }
}
