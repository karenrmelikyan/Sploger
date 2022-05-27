<?php

namespace App\Providers;

use App\Events\ProjectCreated;
use App\Events\SplogCreated;
use App\Events\SplogDeleted;
use App\Events\SplogDeployed;
use App\Events\SplogInstanceCreated;
use App\Listeners\ChangeSplogStatusToActive;
use App\Listeners\CrawlBingUrlsForKeywords;
use App\Listeners\CreateWebApplication;
use App\Listeners\DeleteWebApplication;
use App\Listeners\DeploySplogTemplate;
use App\Listeners\ScheduleSplogPosts;
use App\Listeners\StoreSplogInstanceData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SplogCreated::class => [
            CreateWebApplication::class,
        ],
        SplogDeleted::class => [
            DeleteWebApplication::class,
        ],
        SplogInstanceCreated::class => [
            DeploySplogTemplate::class,
            StoreSplogInstanceData::class,
        ],
        SplogDeployed::class => [
            ChangeSplogStatusToActive::class,
            ScheduleSplogPosts::class,
        ],
        ProjectCreated::class => [
            CrawlBingUrlsForKeywords::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
