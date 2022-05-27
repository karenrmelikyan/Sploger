<?php

declare(strict_types=1);

namespace App\Services;

use ErrorException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use RuntimeException;

use function array_key_exists;
use function basename;
use function file_get_contents;

final class WordpressService
{
    private ?string $token = null;
    private ?string $host = null;

    public function __construct(private readonly ClientInterface $http)
    {
        //
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function authorize(string $host, string $username, string $password): void
    {
        $response = $this->post(
            "$host/wp-json/jwt-auth/v1/token",
            [
                'username' => $username,
                'password' => $password,
            ]
        );

        if (!array_key_exists('token', $response)) {
            throw new RuntimeException('Token not found in authorization response');
        }

        $this->host = $host;
        $this->token = $response['token'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function createPost(string $title, string $slug, string $content, ?int $featuredMediaId = null): array
    {
        $this->authenticate();
        return $this->post(
            "$this->host/wp-json/wp/v2/posts",
            [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'status' => 'publish',
                'featured_media' => $featuredMediaId
            ],
            $this->token
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function listPosts(string $orderBy = 'date', int $perPage = 10): array
    {
        $this->authenticate();
        return $this->get(
            "$this->host/wp-json/wp/v2/posts",
            [
                'orderby' => $orderBy,
                'per_page' => $perPage,
            ],
            $this->token
        );
    }

    /**
     * @throws ErrorException
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function createMediaItem(string $imageUrl, string $imageAlt): array
    {
        $this->authenticate();
        $fileContents = file_get_contents($imageUrl);

        $stream = new MultipartStream([
            [
                'name' => 'file',
                'contents' => $fileContents,
                'filename' => basename($imageUrl),
            ],
            [
                'name' => 'alt_text',
                'contents' => $imageAlt,
            ],
            [
                'name' => 'title',
                'contents' => $imageAlt,
            ],
            [
                'name' => 'comment_status',
                'contents' => 'closed',
            ],
            [
                'name' => 'ping_status',
                'contents' => 'closed',
            ],
            [
                'name' => 'status',
                'contents' => 'publish',
            ],
            [
                'name' => 'caption',
                'contents' => '',
            ],
            [
                'name' => 'description',
                'contents' => '',
            ],
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'multipart/form-data; boundary=' . $stream->getBoundary(),
        ];

        $request = new Request('POST', "$this->host/wp-json/wp/v2/media", $headers, $stream);

        $response = $this->http->sendRequest($request);
        if ($response->getStatusCode() > 400) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }

        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    private function authenticate(): void
    {
        if ($this->host === null || $this->token === null) {
            throw new RuntimeException('You need to authorize first.');
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function post(string $uri, array $data = [], string $token = null): array
    {
        // Remove null values from data
        $data = array_filter($data, static fn($value) => $value !== null);
        $headers = [];
        if ($token !== null) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $request = new Request('POST', $uri, $headers, json_encode($data, JSON_THROW_ON_ERROR));
        $response = $this->http->sendRequest($request);
        if ($response->getStatusCode() > 400) {
            throw new BadResponseException($response->getReasonPhrase(), $request, $response);
        }
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function get(string $uri, array $query, string $token = null): array
    {
        if (count($query) > 0) {
            $uri .= '?' . http_build_query($query);
        }

        $headers = [];
        if ($token !== null) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = $this->http->sendRequest(new Request('GET', $uri, $headers));

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
