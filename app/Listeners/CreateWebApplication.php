<?php

namespace App\Listeners;

use App\Events\SplogCreated;
use App\Jobs\CreateSplogInstance;

final class CreateWebApplication
{
    public function __construct()
    {
        //
    }

    public function handle(SplogCreated $event): void
    {
        CreateSplogInstance::dispatch($event->splog->domain, $event->splog->server_id ?? $event->splog->project->server_id);
    }
}
