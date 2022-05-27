<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\KeywordSet;
use App\Models\Setting;
use App\Repositories\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class SettingsRepository implements SettingsRepositoryInterface
{
    public function __construct(private Setting $model)
    {
        //
    }

    public function findAll(): array|Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Setting
    {
        return $this->model->find($id);
    }

    public function findByName(string $name): ?Setting
    {
        return $this->model->firstWhere(['name' => $name]);
    }
}
