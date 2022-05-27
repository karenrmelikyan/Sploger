<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\API\ServerRepository;
use App\Services\GreedyProxyCrawler;
use App\Services\GuzzleRateLimiter\CacheStore;
use App\Services\GuzzleRateLimiter\RateLimitMiddleware;
use App\Services\RunCloud;
use App\Services\RunCloudService;
use App\Services\WordpressService;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

use function config;

final class HttpClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(WordpressService::class)
            ->needs(ClientInterface::class)
            ->give(static function() {
                return new Client([
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ]);
            });

        $this->app
            ->when([RunCloud\WebApplicationService::class, RunCloud\SecurityService::class])
            ->needs(ClientInterface::class)
            ->give(static function(Application $app) {
                $stack = HandlerStack::create();

                $stack->push(
                    RateLimitMiddleware::perHeaders(
                        'X-RateLimit-Remaining',
                        'X-RateLimit-Limit',
                        store: $app->get(CacheStore::class)
                    )
                );
                $stack->push(GuzzleRetryMiddleware::factory());

                return new Client([
                    'handler' => $stack,
                    'auth' => [
                        config('auth.runcloud.username'),
                        config('auth.runcloud.password'),
                    ],
                    'connect_timeout' => 15,
                    'read_timeout' => 30,
                    'timeout' => 30,
                ]);
            });

        $this->app->when([ServerRepository::class, RunCloudService::class])
            ->needs(ClientInterface::class)
            ->give(static function() {
                return new Client([
                    'base_uri' => 'https://manage.runcloud.io/api/v2/',
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [
                        config('auth.runcloud.username'),
                        config('auth.runcloud.password'),
                    ],
                    'connect_timeout' => 15,
                    'read_timeout' => 30,
                    'timeout' => 30,
                ]);
            });

        $this->app->bind(GreedyProxyCrawler::class, static function (Application $app) {
            return new GreedyProxyCrawler(
                config('auth.proxycrawl.token'),
                config('auth.proxycrawl.js_token'),
                new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36 Edg/89.0.774.68',
                        'Accept-Encoding' => 'gzip,deflate',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Cache-Control' => 'no-cache',
                        'Preferanonymous' => 1,
                    ],
                    'connect_timeout' => 15,
                    'read_timeout' => 30,
                    'timeout' => 30,
                ]),
                $app->make(LoggerInterface::class),
            );
        });

    }
}
