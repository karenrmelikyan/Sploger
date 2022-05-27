<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @return User
     */
    public function create(string $name, string $email, string $password): User;

    public function update(User $user): bool;

    /**
     * @param int $id
     */
    public function delete(int $id): void;

    /**
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    public function findAllPaginated($perPage = 50): LengthAwarePaginator;
}
