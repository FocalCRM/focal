<?php

declare(strict_types=1);

namespace Focal\Core;

use Focal\Core\Support\Enrichment\EnrichmentManager;
use Focal\Core\Support\LifecycleStateMachine;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/focal-core.php', 'focal-core');

        $this->app->singleton(LifecycleStateMachine::class, function (): LifecycleStateMachine {
            return new LifecycleStateMachine;
        });

        $this->app->singleton(EnrichmentManager::class, function (): EnrichmentManager {
            return new EnrichmentManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/focal-core.php' => config_path('focal-core.php'),
            ], 'focal-core-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'focal-core-migrations');
        }
    }
}
