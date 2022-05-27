<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Server;
use App\Models\WebApplication;

interface ServerRepositoryInterface
{
    public function listServers(): array;
}
