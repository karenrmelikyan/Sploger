<?php

declare(strict_types=1);

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;

/**
 * Class Article
 *
 * @property int $keyword_id
 * @property string|null $url
 * @property string|null $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Keyword $keyword
 *
 * @method null|self first(array|string $columns = ['*'])
 * @method null|self firstWhere(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method Collection|self[] get(array|string $columns = ['*'])
 * @method Builder|self where(Closure|string|array|Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class Article extends Model
{
    use HasFactory;

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
