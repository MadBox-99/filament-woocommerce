<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;

final class WooSyncStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('Active stores'), (string) WooStore::query()->where('is_active', true)->count()),
            Stat::make(__('Mapped records'), (string) WooMapping::query()->count()),
            Stat::make(
                __('Last sync'),
                (string) (WooSyncLog::query()->latest()->value('finished_at')?->diffForHumans() ?? __('Never')),
            ),
            Stat::make(
                __('Failed last 24h'),
                (string) WooSyncLog::query()
                    ->where('status', WooSyncLog::STATUS_FAILED)
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
            ),
        ];
    }
}
