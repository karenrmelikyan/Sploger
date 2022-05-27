<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SplogInstanceCreated;
use App\Models\Splog;

final class StoreSplogInstanceData
{
    public function handle(SplogInstanceCreated $event): void
    {
        // TODO: refactor to repository pattern
        /** @var Splog $splog */
        $splog = Splog::where(['domain' => $event->instance->domain])->first();
        $splog->web_application_id = $event->instance->runCloudAppId;
        $splog->database_id = $event->instance->dbRunCloudId;
        $splog->instance_status = Splog::STATUS_IN_PROGRESS;
        $splog->save();
    }
}
