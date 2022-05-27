<?php

namespace App\Models;

use App\Behavior\Filterable;
use App\Behavior\Sortable;
use App\Enums\CacheStatus;
use Closure;
use Doctrine\Common\Cache\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class Keyword
 * @property int $id
 * @property string $name
 * @property string $language_code
 * @property CacheStatus $article_cache_status
 * @property CacheStatus $markov_cache_status
 *
 * Relations
 * @property KeywordSet[] $sets
 * @property Article[] $articles
 *
 * @method static self|\Illuminate\Database\Eloquent\Builder where(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder selectRaw(string $expression, array $bindings = [])
 * @method static int count(string $columns = '*')
 * @method self firstOrCreate(array $attributes = [], array $values = [])
 * @method null|self first(array|string $columns = ['*'])
 * @method null|self firstWhere(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method self|\Illuminate\Database\Eloquent\Builder with(string|array $relations, string|Closure $callback = null)
 * @method LengthAwarePaginator paginate(int $perPage, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 * @method self|\Illuminate\Database\Eloquent\Builder when(mixed $value, callable $callback, callable $default = null)
 * @method self|\Illuminate\Database\Eloquent\Builder whereHas(string $relation, Closure|null $callback = null, string $operator = '>=', int $count = 1)
 * @method self|null find(string|int $id, array $columns = ['*'])
 */
class Keyword extends Model
{
    use HasFactory;
    use Sortable;
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'language_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'article_cache_status' => CacheStatus::class,
        'markov_cache_status' => CacheStatus::class,
    ];

    public function sets(): BelongsToMany
    {
        return $this
            ->belongsToMany(KeywordSet::class, 'keyword_set', 'keyword_id', 'set_id')
            ->withTimestamps();
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'keyword_id', 'id');
    }
}
