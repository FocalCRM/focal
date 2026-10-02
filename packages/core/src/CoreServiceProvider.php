<?php

declare(strict_types=1);

namespace Odden\Core;

use Odden\Core\Support\Enrichment\EnrichmentManager;
use Odden\Core\Support\LifecycleStateMachine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/odden-core.php', 'odden-core');

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

        // Shared limiters for Odden's public routes: "odden-public" for browser-facing
        // submissions (forms, chat, portal replies), "odden-api" for token-authenticated
        // server-to-server calls (webhooks, sending APIs). Per IP, per minute.
        RateLimiter::for('odden-public', fn (Request $request): Limit => Limit::perMinute((int) config('odden-core.rate_limits.public', 30))->by((string) $request->ip()));
        RateLimiter::for('odden-api', fn (Request $request): Limit => Limit::perMinute((int) config('odden-core.rate_limits.api', 600))->by((string) $request->ip()));

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/odden-core.php' => config_path('odden-core.php'),
            ], 'odden-core-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'odden-core-migrations');
        }
    }
}
