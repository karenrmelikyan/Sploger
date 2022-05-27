<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SplogDeleted;
use App\Jobs\RemoveSplogInstance;
use App\Services\RunCloudService;

final class DeleteWebApplication
{
    public function __construct()
    {
        //
    }

    public function handle(SplogDeleted $event): void
    {
        $splog = $event->splog;

        RemoveSplogInstance::dispatchIf(
            $splog->web_application_id !== null,
            $splog->server_id ?? $splog->project->server_id,
            $splog->web_application_id,
            $splog->database_id,
        );
    }
}
