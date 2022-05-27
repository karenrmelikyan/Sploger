<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SplogDeployed;
use App\Models\Splog;

use function now;

final class ChangeSplogStatusToActive
{
    public function __construct()
    {
    }

    public function handle(SplogDeployed $event): void
    {
        // TODO: refactor to repository pattern
        /** @var Splog $splog */
        $splog = Splog::where(['domain' => $event->instance->domain])->first();
        $splog->instance_status = Splog::STATUS_DEPLOYED;
        $splog->next_post_at = now();
        $splog->save();
    }
}
