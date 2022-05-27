<?php

declare(strict_types=1);

namespace App\Cache;

final class RateLimiter extends \Illuminate\Cache\RateLimiter
{
    /**
     * Decrement the counter for a given key.
     */
    public function reverse($key): int
    {
        $key = $this->cleanRateLimiterKey($key);

        return (int) $this->cache->decrement($key);
    }
}
