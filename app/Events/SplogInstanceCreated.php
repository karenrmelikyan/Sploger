<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SplogInstance;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SplogInstanceCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SplogInstance $instance) {
        //
    }
}
