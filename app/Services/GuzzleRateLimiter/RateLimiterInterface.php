<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface RateLimiterInterface
{
    public function handle(RequestInterface $request, array $options, callable $handler): PromiseInterface;
}
