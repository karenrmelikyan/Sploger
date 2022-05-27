<?php

namespace App\Models;

use App\Behavior\Filterable;
use App\Behavior\Sortable;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Class KeywordSet
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Keyword[]|Collection $keywords
 * @property Project[]|Collection $projects
 *
 * @method self[]|Collection all($columns = ['*'])
 * @method Builder with(string|array $relations, string|Closure $callback = null)
 * @method Builder when(mixed $value, callable $callback, callable $default = null)
 * @method Builder withCount(array|string $relations)
 * @method self|null find(string|int $id, array $columns = ['*'])
 * @method self orderBy(Closure|Builder|Expression|string $column, string $direction)
 * @method LengthAwarePaginator paginate(int $perPage, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 */
class KeywordSet extends Model
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
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this
            ->belongsToMany(Keyword::class, 'keyword_set', 'set_id', 'keyword_id')
            ->withTimestamps();
    }

    public function getKeywordsCount(): int
    {
        return $this->keywords_count ?? $this->loadCount('keywords')->getAttribute('keywords_count');
    }
}
