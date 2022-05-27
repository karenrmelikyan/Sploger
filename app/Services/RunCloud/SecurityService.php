<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

use function array_diff;
use function array_filter;
use function array_map;
use function count;

final class SecurityService extends BaseService
{
    private const REQUIRED_PORTS = ['tcp' => [80, 443, 22]];

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function listFirewallRules(int $serverId): array
    {
        return $this->get("servers/$serverId/security/firewalls")['data'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @noinspection PhpSameParameterValueInspection
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function createFirewallRule(int $serverId, FirewallRuleType $type, int $port, Protocol $protocol): array
    {
        return $this->post("servers/$serverId/security/firewalls", [
            'type' => $type->value,
            'port' => $port,
            'protocol' => $protocol->value,
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function deployRulesToServer(int $serverId): void
    {
        $this->put("servers/$serverId/security/firewalls");
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function deployRequiredRules(int $serverId): void
    {
        $missingPorts = $this->getMissingRequiredRules($serverId);
        if (count($missingPorts) === 0) {
            return;
        }

        foreach ($missingPorts as $port) {
            $this->createFirewallRule($serverId, FirewallRuleType::GLOBAL, $port, Protocol::TCP);
        }
        $this->deployRulesToServer($serverId);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function hasMissingRequiredRules(int $serverId): bool
    {
        return count($this->getMissingRequiredRules($serverId)) !== 0;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function getMissingRequiredRules(int $serverId): array
    {
        $ports = array_map(
            static fn(array $rule) => $rule['port'],
            array_filter(
                $this->listFirewallRules($serverId),
                static fn(array $rule) => $rule['type'] === 'global' && $rule['protocol'] === 'tcp')
        );

        return array_diff(self::REQUIRED_PORTS['tcp'], $ports);
    }
}
