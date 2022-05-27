<?php

declare(strict_types=1);

namespace App\Behavior;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

use function array_filter;
use function count;
use function explode;
use function in_array;
use function ltrim;
use function str_contains;
use function substr;

use function trim;

use const ARRAY_FILTER_USE_KEY;

/**
 * @method self filterable(array $except = [])
 */
trait Filterable
{
    /**
     * Scope a query to apply sorting.
     *
     * @param Builder $query
     * @param array $except
     * @return Builder
     */
    public function scopeFilterable(Builder $query, array $except = []): Builder
    {
        $request = request();

        if ($request->query->has('filter')) {
            $params = array_filter($request->except(['page'])['filter'], static function ($key) use ($except) {
                return !in_array($key, $except, true);
            }, ARRAY_FILTER_USE_KEY);
            return $this->filterQueryBuilder($query, $params);
        }

        return $query;
    }

    private function filterQueryBuilder(Builder $query, array $filters): Builder
    {
        /** @var Model $model */
        $model = $this;

        foreach ($filters as $column => $value) {
            $relation = null;
            $relationName = null;

            if (str_contains($column, '.')) {
                $explodedResult = explode('.', $column);
                if (count($explodedResult) !== 2) {
                    throw new Exception('Only table and direct relation columns can be used for sorting.');
                }
                [$relationName, $column] = $explodedResult;

                $relation = $query->getRelation($relationName);
                $model = $relation->getRelated();
            }

            $allowedOperators = ['>', '<', '=', '>=', '<=', '!=', '<>'];
            $singleCharOperator = $value[0];
            $twoCharOperator = substr($value, 0, 2);

            if ($value !== null && Schema::connection($model->getConnectionName())->hasColumn($model->getTable(), $column)) {
                $callback = static function (Builder $query) use ($column, $value, $allowedOperators, $twoCharOperator, $singleCharOperator) {
                    if(in_array($twoCharOperator, $allowedOperators, true)) {
                        $query->where($column, $twoCharOperator, ltrim(substr($value, 2)));
                    } elseif (in_array($singleCharOperator, $allowedOperators, true)) {
                        $query->where($column, $singleCharOperator, ltrim(substr($value, 1)));
                    } else {
                        $query->where($column, 'like', "%$value%");
                    }
                };

                if ($relation === null) {
                    $query = $query->where($callback);
                } else {
                    /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                    $query = $query->whereHas($relationName, $callback);
                }
            } else {
                $columns =  array_filter($query->toBase()->columns ?? [], static fn ($queryColumn) => $queryColumn instanceof Expression);
                foreach ($columns as $expression) {
                    $segments = explode(' ', (string) $expression);
                    if (count($segments) > 1 && $segments[count($segments) - 2] === 'as' && trim($segments[count($segments) - 1], '`') === $column) {
                        if(in_array($twoCharOperator, $allowedOperators, true)) {
                            $query->having($column, $twoCharOperator, ltrim(substr($value, 2)));
                        } elseif (in_array($singleCharOperator, $allowedOperators, true)) {
                            $query->having($column, $singleCharOperator, ltrim(substr($value, 1)));
                        } else {
                            $query->having($column, 'like', "%$value%");
                        }
                    }
                }
            }
        }

        return $query;
    }
}
