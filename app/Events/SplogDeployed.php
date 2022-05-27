<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SplogInstance;
use Illuminate\Queue\SerializesModels;

final class SplogDeployed
{
    use SerializesModels;

    public function __construct(public SplogInstance $instance)
    {
        //
    }
}
