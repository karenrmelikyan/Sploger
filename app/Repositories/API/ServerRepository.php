<?php

declare(strict_types=1);

namespace App\Repositories\API;

use App\Models\RunCloud\Server;
use App\Repositories\ServerRepositoryInterface;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

final class ServerRepository extends RunCloudRepository implements ServerRepositoryInterface
{
    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function listServers(): array
    {
        $items = $this->get('servers');

        return \array_map(static function(array $value) {
            return new Server(
                $value['name'],
                $value['ipAddress'],
                $value['provider'],
                $value['id'],
                $value['os'],
                $value['osVersion'],
                $value['connected'],
                $value['online'],
                $value['agentVersion'],
                $value['phpCLIVersion'],
                $value['softwareUpdate'],
                $value['securityUpdate'],
                $value['transferStatus'],
                $value['created_at'],
            );
        }, $items['data']);
    }
}
