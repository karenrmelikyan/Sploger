<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

use Psr\Http\Message\RequestInterface;

class RateLimitMiddleware
{
    private function __construct(private RateLimiterInterface $rateLimiter)
    {
        //
    }

    public static function perHeaders(
        string $remaining,
        string|int $quota,
        string|int $windowSizeMs = 60000,
        ?StoreInterface $store = null,
    ): static
    {
        $rateLimiter = new HeaderBasedRateLimiter($remaining, $quota, $windowSizeMs, $store ?? new InMemoryStore());

        return new static($rateLimiter);
    }

    public function __invoke(callable $handler): callable
    {
        return fn(RequestInterface $request, array $options) => $this->rateLimiter->handle($request, $options, $handler);
    }
}
