<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Splog;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SplogDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Splog $splog)
    {
        //
    }
}
