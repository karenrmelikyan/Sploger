<?php

declare(strict_types=1);

namespace App\Enums;

enum CacheStatus: int
{
    case EMPTY = 0;
    case PENDING = 2;
    case CACHED = 1;

    public function status(): string
    {
        return match($this) {
            self::EMPTY => 'Empty',
            self::CACHED => 'Cached',
            self::PENDING => 'Pending',
        };
    }
}
