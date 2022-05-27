<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebApplication;
use DateTimeImmutable;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function http_build_query;

final class RunCloudService
{
    public function __construct(private ClientInterface $http)
    {
        //
    }

    public function createWebApplication(int $serverId, WebApplication $webApp): WebApplication
    {
        $data = [
            'name' => $webApp->name,
            'domainName' => $webApp->domainName,
            'user' => $webApp->user,
            'phpVersion' => $webApp->phpVersion,
            'stack' => $webApp->stack,
            'stackMode' => $webApp->stackMode,
            'clickjackingProtection' => $webApp->clickjackingProtection,
            'xssProtection' => $webApp->xssProtection,
            'mimeSniffingProtection' => $webApp->mimeSniffingProtection,
            'processManager' => $webApp->processManager,
            'processManagerMaxChildren' => $webApp->processManagerMaxChildren,
            'processManagerMaxRequests' => $webApp->processManagerMaxRequests,
            'timezone' => $webApp->timezone,
            'maxExecutionTime' => $webApp->maxExecutionTime,
            'maxInputTime' => $webApp->maxInputTime,
            'maxInputVars' => $webApp->maxInputVars,
            'memoryLimit' => $webApp->memoryLimit,
            'postMaxSize' => $webApp->postMaxSize,
            'uploadMaxFilesize' => $webApp->uploadMaxFilesize,
            'sessionGcMaxlifetime' => $webApp->sessionGcMaxlifetime,
            'allowUrlFopen' => $webApp->allowUrlFopen,
        ];

        $response = $this->post("servers/$serverId/webapps/custom", $data);
        $webApp->id = $response['id'];
        $webApp->createdAt = new DateTimeImmutable($response['created_at']);

        return $webApp;
    }

    public function deleteWebApplication(int $serverId, int $webAppId): array
    {
        return $this->delete("servers/$serverId/webapps/$webAppId");
    }

    public function getWebApplication(int $serverId, int $webAppId)
    {
        //
    }

    public function getServerIpAddress(int $serverId): string
    {
        $response = $this->get("servers/$serverId");
        return $response['ipAddress'];
    }

    public function createSystemUser(int $serverId, string $username, string $password): int
    {
        $response = $this->post("servers/$serverId/users", [
            'username' => $username,
            'password' => $password,
        ]);

        return $response['id'];
    }

    public function deleteSystemUser(int $serverId, int $userId): void
    {
        $this->delete("servers/$serverId/users/$userId");
    }

    public function listSystemUsers(int $serverId): array
    {
        $items = $this->get("servers/$serverId/users");

        return $items['data'];
    }

    public function installPHPScript(int $serverId, int $webAppId, string $name): int
    {
        $response = $this->post("servers/$serverId/webapps/$webAppId/installer", ['name' => $name]);

        return $response['id'];
    }

    public function createDatabase(int $serverId, string $name, ?string $collation = null): int
    {
        $response = $this->post("servers/$serverId/databases", [
            'name' => $name,
            'collation' => $collation,
        ]);

        return $response['id'];
    }

    public function deleteDatabase(int $serverId, int $databaseId, bool $deleteUser = true): void
    {
        $this->delete("servers/$serverId/databases/$databaseId", ['deleteUser' => $deleteUser]);
    }

    public function createDatabaseUser(int $serverId, string $username, string $password): int
    {
        $response = $this->post("servers/$serverId/databaseusers", [
            'username' => $username,
            'password' => $password,
        ]);

        return $response['id'];
    }

    public function deleteDatabaseUser(int $serverId, int $databaseUserId): void
    {
        $this->delete("servers/$serverId/databaseusers/$databaseUserId");
    }

    public function attachUserToDb(int $serverId, int $databaseId, int $userId): void
    {
        $this->post("servers/$serverId/databases/$databaseId/grant", [
            'id' => $userId,
        ]);
    }

    /**
     * @param string $uri
     * @param array $queryParameters
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function get(string $uri, array $queryParameters = []): array
    {
        $queryString = http_build_query($queryParameters);
        if ($queryString !== '') {
            $uri .= "?$queryString";
        }

        $response = $this->http->sendRequest(new Request('GET', $uri));
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $uri
     * @param array $data
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function post(string $uri, array $data = []): array
    {
        // Remove null values from data
        $data = array_filter($data, static fn($value) => $value !== null);

        $request = new Request('POST', $uri, [], json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->http->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
//            {
//              "message": "The given data was invalid.",
//              "errors": {
//                    "username": [
//                        "The username field is required."
//                    ]
//              }
//            }
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    private function delete(string $uri, array $data = []): array
    {
        $data = array_filter($data, static fn($value) => $value !== null);

        $request = new Request('DELETE', $uri, [], json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->http->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }
}
