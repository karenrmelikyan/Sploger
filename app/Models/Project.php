<?php

namespace App\Models;

use App\Behavior\Filterable;
use App\Behavior\Sortable;
use App\Events\ProjectCreated;
use App\Events\ProjectDeleted;
use App\Events\SplogDeleted;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

use function event;

/**
 * Class Project
 * @property int $id
 * @property string $name
 * @property int $keyword_set_id
 * @property int $server_id
 * @property int $sections_from
 * @property int $sections_to
 * @property int $words_from
 * @property int $words_to
 * @property int|null $keyword_density
 * @property int|null $schedule_interval
 * @property int|null $schedule_variance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $splogs_count
 *
 * @property KeywordSet $keywordSet
 * @property Splog[]|Collection $splogs
 *
 * @method static Builder selectRaw(string $expression, array $bindings = [])
 * @method static int count(string $columns = '*')
 * @method self|null find(string|int $id, array $columns = ['*'])
 * @method self orderBy(Closure|Builder|Expression|string $column, string $direction)
 * @method LengthAwarePaginator paginate(int $perPage, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 * @method \Illuminate\Database\Eloquent\Builder when(mixed $value, callable $callback, callable $default = null)
 */
class Project extends Model
{
    use HasFactory;
    use Notifiable;
    use Sortable;
    use Filterable;

    protected $fillable = [
        'name',
        'keyword_set_id',
        'server_id',
        'sections_from',
        'sections_to',
        'words_from',
        'words_to',
        'keyword_density',
        'schedule_interval',
        'schedule_variance',
    ];

    protected $appends = [
        'splogs_count' => null,
    ];

    protected $dispatchesEvents = [
        'created' => ProjectCreated::class,
        'deleted' => ProjectDeleted::class,
    ];

    public function keywordSet(): BelongsTo
    {
        return $this->belongsTo(KeywordSet::class);
    }

    public function splogs(): HasMany
    {
        return $this->hasMany(Splog::class, 'project_id', 'id');
    }

    public function getLanguageName(): string
    {
        /** @var array $languages */
        $languages = require(storage_path('app/languages.php'));

        return $languages[$this->language];
    }

    public function getSplogsCountAttribute(): int
    {
        return array_key_exists('splogs_count', $this->attributes)
            ? $this->attributes['splogs_count']
            : $this->loadCount('splogs')->attributes['splogs_count'];
    }

    protected static function booted(): void
    {
        static::deleting(static function (Project $project) {
            $project->splogs()->each(function (Splog $splog) {
                event(new SplogDeleted($splog));
            });
        });
    }
}
