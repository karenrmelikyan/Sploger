<?php

declare(strict_types=1);

namespace App\Providers;

use App\Cache\RateLimiter;
use App\Jobs\CreateSplogPost;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter as Limiter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->extend(\Illuminate\Cache\RateLimiter::class, static function ($service, $app) {
            return new RateLimiter($app->make('cache')->driver(
                $app['config']->get('cache.limiter')
            ));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.bootstrap-5');
        Paginator::defaultSimpleView('pagination.bootstrap-5');

        Limiter::for('server-requests', static function (CreateSplogPost $job) {
            return Limit::perMinute(60)->by($job->serverIp);
        });
    }

    public function provides(): array
    {
        return [\Illuminate\Cache\RateLimiter::class];
    }
}
