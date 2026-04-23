<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\WooStoreResource;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\WooSyncLogResource;
use Madbox99\FilamentWooCommerce\Filament\Widgets\WooSyncStatsWidget;

final class FilamentWooCommercePlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'filament-woocommerce';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            WooStoreResource::class,
            WooSyncLogResource::class,
        ]);

        $panel->widgets([
            WooSyncStatsWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
