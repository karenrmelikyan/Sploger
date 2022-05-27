<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Psr7\Request;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

use function array_merge;
use function http_build_query;

final class GreedyProxyCrawler
{
    private const PUBLIC_PROXYCRAWL_API_URL = 'https://api.proxycrawl.com/';

    public function __construct(
        private string $token,
        private string $jsToken,
        private ClientInterface $http,
        private LoggerInterface $logger
    ) {
        //
    }

    /**
     * @param string $url
     * @param array $queryOptions
     * @return string
     * @throws ClientExceptionInterface
     */
    public function get(string $url, array $queryOptions = []): string
    {
        $response = $this->request($url, ['method' => 'GET', 'query' => $queryOptions]);
        return $response->getBody()->getContents();
    }

    /**
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     */
    private function request(string $url, array $options = []): ResponseInterface
    {
        $method = $options['method'] ?? 'GET';
        $request = new Request($method, $this->buildUrl($url, $options['query'] ?? []));
        $response = $this->http->sendRequest($request);
        $statusCode = $response->getStatusCode();

        if ($statusCode > 400 && $statusCode !== 404) {
            $options['query'] = array_merge($options['query'], [
                'token' => $this->token,
                'url' => $url,
            ]);
            $this->logger->critical("Using ProxyCrawl ($url), previous status: $statusCode");

            $request = new Request($method, $this->buildUrl(self::PUBLIC_PROXYCRAWL_API_URL, $options['query']));
            $response = $this->http->sendRequest($request);
            if ($response->getStatusCode() !== 200) {
                $this->logger->critical('ProxyCrawl bad response, reason: ' . $response->getReasonPhrase() . ', status: ' . $response->getStatusCode());
                // TODO: ProxyCrawl can return 429 on rate limit
                throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
            }
        }

        return $response;
    }

    #[Pure]
    private function buildUrl(string $url, array $queryOptions = []): string
    {
        if (count($queryOptions) === 0) {
            return $url;
        }

        return $url . '?' . http_build_query($queryOptions);
    }
}
