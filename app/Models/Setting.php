<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Class Setting
 * @property int $id
 * @property string $name
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method self[]|Collection all($columns = ['*'])
 * @method self|null find(string|int $id, array $columns = ['*'])
 * @method null|self firstWhere(\Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class Setting extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'value',
    ];
}
