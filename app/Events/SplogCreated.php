<?php

namespace App\Events;

use App\Models\Splog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SplogCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Splog $splog)
    {
        //
    }
}
