<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;

class CacheStore implements StoreInterface
{
    public function __construct(private Repository $store)
    {
        //
    }

    public function set(string $key, mixed $value): void
    {
        $this->store->put($key, $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        return $this->store->get($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->store->has($key);
    }
}
