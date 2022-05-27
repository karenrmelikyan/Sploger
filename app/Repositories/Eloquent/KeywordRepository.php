<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Keyword;
use App\Repositories\KeywordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class KeywordRepository implements KeywordRepositoryInterface
{
    public function __construct(private Keyword $model)
    {
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function bulkCreate(array $keywords, string $language_code, bool $localTransaction = true): array
    {
        $models = [];
        $connection = $this->model->getConnection();

        if ($localTransaction || $connection->transactionLevel() === 0) {
            $connection->beginTransaction();
        }

        try {
            foreach ($keywords as $keyword) {
                $models[] = $this->model->firstOrCreate(['name' => $keyword, 'language_code' => $language_code]);
            }

            if ($localTransaction || $connection->transactionLevel() === 0) {
                $connection->commit();
            }
        } catch (Throwable $t) {
            $connection->rollBack();
            throw $t;
        }

        return $models;
    }

    public function findByName(string $name, string $languageCode): ?Keyword
    {
        return $this->model->firstWhere(['name' => $name, 'language_code' => $languageCode]);
    }

    public function findById(int $id): ?Keyword
    {
        return $this->model->find($id);
    }

    public function delete(int $id): void
    {
        $model = $this->model->find($id);

        if ($model === null) {
            throw new ModelNotFoundException('Model not found.');
        }

        $model->delete();
    }

    public function findBySet(int $setId, int $perPage = 50, bool $withArticlesCount = false): LengthAwarePaginator
    {
        return $this->model
            ->whereHas('sets', static function (Builder $query) use ($setId) {
                $query->where('keyword_set.set_id', '=', $setId);
            })->leftJoin('markov_matrix', 'keywords.id', '=', 'markov_matrix.keyword_id')
            ->select([
                'keywords.*',
                'markov_matrix.tokens as markov_tokens',
                'markov_matrix.distinct_tokens as markov_distinct_tokens',
            ])->when($withArticlesCount, static function (Builder $query) {
                return $query->withCount(['articles' => static function (Builder $query) {
                    $query->whereNotNull('articles.content');
                }]);
            })
            ->sortable()->filterable()->paginate($perPage);
    }
}
