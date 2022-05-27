<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private User $model, private Hasher $hash)
    {
    }

    public function create(string $name, string $email, string $password): User
    {
        $user = $this->model->newInstance();
        $user->name = $name;
        $user->email = $email;
        $user->password = $this->hash->make($password);

        $user->save();

        return $user;
    }

    public function update(User $user): bool
    {
        return $user->save();
    }

    /**
     * @param int $id
     * @throws Exception
     */
    public function delete(int $id): void
    {
        $model = $this->findById($id);
        if ($model === null) {
            throw new ModelNotFoundException('Model not found.');
        }
        $model->delete();
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findAllPaginated($perPage = 50): LengthAwarePaginator
    {
        return $this->model
            ->sortable()
            ->filterable()
            ->paginate($perPage);
    }
}
