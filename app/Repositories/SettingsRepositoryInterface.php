<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

interface SettingsRepositoryInterface
{
    public function findById(int $id): ?Setting;

    public function findByName(string $name): ?Setting;

    /**
     * @return Setting[]|Collection
     */
    public function findAll(): array|Collection;
}
