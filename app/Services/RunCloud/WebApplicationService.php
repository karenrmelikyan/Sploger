<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

final class WebApplicationService extends BaseService
{
    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function addDomainName(int $serverId, int $webAppId, string $domain, DomainType $type = DomainType::ALIAS): array
    {
        return $this->post("servers/$serverId/webapps/$webAppId/domains", [
            'name' => $domain,
            'type' => $type->value,
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function deleteDomainName(int $serverId, int $webAppId, int $domainId): array
    {
        return $this->delete("servers/$serverId/webapps/$webAppId/domains/$domainId");
    }
}
