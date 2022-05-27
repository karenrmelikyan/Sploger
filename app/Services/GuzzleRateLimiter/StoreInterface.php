<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

interface StoreInterface
{
    public function set(string $key, mixed $value): void;
    public function get(string $key): mixed;
    public function has(string $key): bool;
}
