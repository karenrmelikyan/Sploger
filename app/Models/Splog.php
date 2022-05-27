<?php

namespace App\Models;

use App\Behavior\Filterable;
use App\Behavior\Sortable;
use App\Events\SplogCreated;
use App\Events\SplogDeleted;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Notifications\Notifiable;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Splog
 * @property int $id
 * @property string $domain
 * @property int $server_id
 * @property int $sections_from
 * @property int $sections_to
 * @property int $words_from
 * @property int $words_to
 * @property int $project_id
 * @property int $web_application_id
 * @property int $database_id
 * @property int $instance_status
 * @property Carbon|null $next_post_at
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Project $project
 *
 * @method static \Illuminate\Database\Query\Builder selectRaw(string $expression, array $bindings = [])
 * @method static int count(string $columns = '*')
 * @method self|null find(string|int $id, array $columns = ['*'])
 * @method static Builder where(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method null|self firstWhere(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method Builder when(mixed $value, callable $callback, callable $default = null)
 */
class Splog extends Model
{
    use HasFactory;
    use Notifiable;
    use Sortable;
    use Filterable;

    public const STATUS_INACTIVE = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_DEPLOYED = 2;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'next_post_at' => 'datetime',
    ];

    protected $fillable = [
        'domain',
        'server_id',
        'sections_from',
        'sections_to',
        'words_from',
        'words_to',
    ];

    protected $dispatchesEvents = [
        'created' => SplogCreated::class,
        'deleted' => SplogDeleted::class,
    ];

    #[ArrayShape([
        self::STATUS_INACTIVE => "string",
        self::STATUS_IN_PROGRESS => "string",
        self::STATUS_DEPLOYED => "string"
    ])]
    public static function statuses(): array
    {
        return [
            self::STATUS_INACTIVE => __('Inactive'),
            self::STATUS_IN_PROGRESS => __('In Progress'),
            self::STATUS_DEPLOYED => __('Deployed'),
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function getStatusText(): string
    {
        return match($this->instance_status) {
            self::STATUS_INACTIVE => self::statuses()[self::STATUS_INACTIVE],
            self::STATUS_IN_PROGRESS => self::statuses()[self::STATUS_IN_PROGRESS],
            self::STATUS_DEPLOYED => self::statuses()[self::STATUS_DEPLOYED],
        };
    }

    public function getStatusColor(): string
    {
        return match($this->instance_status) {
            self::STATUS_INACTIVE => '#ff0000',
            self::STATUS_IN_PROGRESS => '#ffa500',
            self::STATUS_DEPLOYED => '#008000',
        };
    }


}
