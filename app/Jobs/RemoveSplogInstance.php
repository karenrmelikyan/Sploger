<?php

namespace App\Jobs;

use App\Services\RunCloudService;
use Illuminate\Bus\Queueable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveSplogInstance implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private int $serverId,
        private int $webApplicationId,
        private ?int $databaseId = null,
    ) {
        $this->onQueue('splog-deployer');
    }

    public function handle(RunCloudService $runcloud, Repository $config): void
    {
        // we can delete only user, which will also delete web application if store system user id
        $webAppData = $runcloud->deleteWebApplication($this->serverId, $this->webApplicationId);
        $runcloud->deleteSystemUser($this->serverId, $webAppData['server_user_id']);
        if ($this->databaseId !== null) {
            $runcloud->deleteDatabase($this->serverId, $this->databaseId);
        }
        unset($config['database.connections.' . $this->databaseId]);
    }

    public function displayName(): string
    {
        return 'Removing splog instance with web application id: ' . $this->webApplicationId;
    }
}
