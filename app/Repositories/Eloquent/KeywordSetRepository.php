<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\KeywordSet;
use App\Repositories\KeywordSetRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Throwable;

final class KeywordSetRepository implements KeywordSetRepositoryInterface
{
    public function __construct(private KeywordSet $model, private ConnectionInterface $db)
    {
    }

    public function create(string $name, string $keywords): KeywordSet
    {
        $set = $this->model->newInstance();
        $set->name = $name;
        $set->keywords = $keywords;

        $set->save();

        return $set;
    }

    public function update(KeywordSet $set): bool
    {
        return $set->save();
    }

    /**
     * @param int $id
     * @throws Exception
     * @throws Throwable
     */
    public function delete(int $id): void
    {
        /** @var KeywordSet|null $model */
        $model = $this->model->with(['keywords' => static function (BelongsToMany $query) {
            return $query->withCount('sets');
        }])->find($id);

        if ($model === null) {
            throw new ModelNotFoundException('Model not found.');
        }

        $this->db->transaction(function() use($model) {
            foreach ($model->keywords as $keyword) {
                if ($keyword->getAttribute('sets_count') === 1) {
                    $keyword->delete();
                }
            }
            $model->delete();
        });
    }

    public function findById(int $id, bool $withKeywords = false): ?KeywordSet
    {
        /** @var KeywordSet|null $model */
        $model = $this->model->when($withKeywords, static function (Builder $query) {
            return $query->with('keywords');
        })->find($id);

        return $model;
    }

    public function findAllPaginated(int $perPage = 50): LengthAwarePaginator
    {
        return $this->model
            ->withCount('keywords')
            ->sortable()
            ->filterable()
            ->paginate($perPage);
    }

    public function findAll(): array|Collection
    {
        return $this->model->all();
    }
}
