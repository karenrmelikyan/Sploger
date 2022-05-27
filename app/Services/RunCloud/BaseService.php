<?php

declare(strict_types=1);

namespace App\Services\RunCloud;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function array_filter;

abstract class BaseService
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
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    protected function get(string $path, array $query = []): array
    {
        $url = self::BASE_URL . '/' . $path;
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $request = new Request('GET', $url, self::DEFAULT_HEADERS);
        $response = $this->client->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    protected function post(string $path, array $data = []): array
    {
        $url = self::BASE_URL . '/' . $path;

        // Remove null values from data
        $data = array_filter($data, static fn($value) => $value !== null);

        $request = new Request('POST', $url, self::DEFAULT_HEADERS, json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    protected function put(string $path, array $data = []): array
    {
        $url = self::BASE_URL . '/' . $path;

        // Remove null values from data
        $data = array_filter($data, static fn($value) => $value !== null);

        $request = new Request('PUT', $url, self::DEFAULT_HEADERS, json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }

        return $response->getBody()->getContents() === ''
            ? []
            : json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    protected function delete(string $path, array $data = []): array
    {
        $url = self::BASE_URL . '/' . $path;

        // Remove null values from data
        $data = array_filter($data, static fn($value) => $value !== null);

        $request = new Request('DELETE', $url, self::DEFAULT_HEADERS, json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
