<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Madbox99\FilamentWooCommerce\Commands\WooSyncCommand;
use Madbox99\FilamentWooCommerce\Jobs\SyncStoreJob;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Sync\SyncRegistry;

final class FilamentWooCommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-woocommerce.php',
            'filament-woocommerce',
        );

        $this->app->singleton(SyncRegistry::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-woocommerce.php' => config_path('filament-woocommerce.php'),
            ], 'filament-woocommerce-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-woocommerce-migrations');

            $this->commands([
                WooSyncCommand::class,
            ]);
        }

        $this->registerSchedule();
    }

    private function registerSchedule(): void
    {
        if (! (bool) config('filament-woocommerce.schedule.enabled', false)) {
            return;
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $cron = (string) config('filament-woocommerce.schedule.cron', '0 * * * *');

            $schedule->call(function (Application $app): void {
                WooStore::query()->where('is_active', true)->each(function (WooStore $store): void {
                    SyncStoreJob::dispatch($store->id);
                });
            })
                ->name('filament-woocommerce:sync-all')
                ->cron($cron)
                ->withoutOverlapping();
        });
    }
}
