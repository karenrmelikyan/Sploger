<?php

namespace App\Providers;

use App\Repositories\{API\ServerRepository,
    ArticleRepositoryInterface,
    JobRepositoryInterface,
    KeywordRepositoryInterface,
    KeywordSetRepositoryInterface,
    ProjectRepositoryInterface,
    ServerRepositoryInterface,
    SettingsRepositoryInterface,
    SplogRepositoryInterface,
    UserRepositoryInterface};
use App\Repositories\Eloquent\{ArticleRepository,
    JobRepository,
    KeywordRepository,
    KeywordSetRepository,
    ProjectRepository,
    SettingsRepository,
    SplogRepository,
    UserRepository};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(KeywordSetRepositoryInterface::class, KeywordSetRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(KeywordRepositoryInterface::class, KeywordRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(SplogRepositoryInterface::class, SplogRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
    }
}
