<?php

declare(strict_types=1);

namespace App\Repositories\API;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function count;
use function http_build_query;

class RunCloudRepository
{
    private const BASE_URL = 'https://manage.runcloud.io/api/v2';
    private const DEFAULT_HEADERS = [
        'Accept' => 'application/json',
		'Content-Type' => 'application/json',
    ];

    public function __construct(private ClientInterface $client)
    {
        //
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    protected function get(string $path, array $query = []): array
    {
        $url = self::BASE_URL . '/' . $path;
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $response = $this->client->sendRequest(new Request('GET', $url, self::DEFAULT_HEADERS));

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
