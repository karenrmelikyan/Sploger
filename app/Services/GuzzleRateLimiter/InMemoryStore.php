<?php

declare(strict_types=1);

namespace App\Services\GuzzleRateLimiter;

use function array_key_exists;

final class InMemoryStore implements StoreInterface
{
    private array $data = [];

    public function __construct()
    {
        //
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}
