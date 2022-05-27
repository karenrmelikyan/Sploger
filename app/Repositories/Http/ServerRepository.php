<?php

declare(strict_types=1);

namespace App\Repositories\Http;

use App\Models\Server;
use App\Repositories\ServerRepositoryInterface;
use GuzzleHttp\Psr7\Request;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function http_build_query;
use function json_decode;

final class ServerRepository implements ServerRepositoryInterface
{
    public function __construct(private ClientInterface $http)
    {
    }

    /**
     * @param string|null $search
     * @param bool $withStats
     * @return Server[]
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function listServers(?string $search = null, bool $withStats = false): array
    {
        $items = $this->get('servers', ['search' => $search]);
        $servers = [];

        foreach ($items['data'] as $server) {
            $server = Server::fromApiResponse($server);
            if ($withStats) {
                $stats = $this->stats($server->getId());
                $server->setWebApplicationsCount($stats['stats']['webApplication']);
                $server->setCountry($stats['country']);
            }
            $servers[] = $server;
        }

        return $servers;
    }

    private function stats(int $id): array
    {
        return $this->get("servers/$id/stats");
    }

    /**
     * @param string $uri
     * @param array $queryParameters
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    #[ArrayShape(['data' => 'array', 'meta' => 'array'])]
    private function get(string $uri, array $queryParameters = []): array
    {
        $queryString = http_build_query($queryParameters);
        if ($queryString !== '') {
            $uri .= "?$queryParameters";
        }

        $response = $this->http->sendRequest(new Request('GET', $uri));
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }
}
