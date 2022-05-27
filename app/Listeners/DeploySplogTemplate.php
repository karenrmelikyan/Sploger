<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SplogInstanceCreated;


final class DeploySplogTemplate
{
    public function __construct()
    {
        //
    }

    public function handle(SplogInstanceCreated $event): void
    {
        \App\Jobs\DeploySplogTemplate::dispatch($event->instance);
    }
}
