<?php

namespace App\Models;

use App\Behavior\Filterable;
use App\Behavior\Sortable;
use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Class User
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method User|null find(string|int $id, array $columns = ['*'])
 * @method User orderBy(Closure|Builder|Expression|string $column, string $direction)
 * @method LengthAwarePaginator paginate(int $perPage, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use Sortable;
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
