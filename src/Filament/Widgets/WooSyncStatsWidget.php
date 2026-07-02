<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;

final class WooSyncStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->getKey();

        /**
         * Constrain a store-owned model to the current tenant's stores.
         * A null tenant (single-tenant panels) leaves the query untouched.
         *
         * @param  Builder<WooMapping|WooSyncLog>  $query
         * @return Builder<WooMapping|WooSyncLog>
         */
        $scopeByStore = fn (Builder $query): Builder => $query->when(
            $tenantId !== null,
            fn (Builder $scoped): Builder => $scoped->whereHas(
                'store',
                fn (Builder $store): Builder => $store->forTenant($tenantId),
            ),
        );

        return [
            Stat::make(
                __('Active stores'),
                (string) WooStore::query()->forTenant($tenantId)->where('is_active', true)->count(),
            ),
            Stat::make(__('Mapped records'), (string) $scopeByStore(WooMapping::query())->count()),
            Stat::make(
                __('Last sync'),
                (string) ($scopeByStore(WooSyncLog::query())->latest()->value('finished_at')?->diffForHumans() ?? __('Never')),
            ),
            Stat::make(
                __('Failed last 24h'),
                (string) $scopeByStore(WooSyncLog::query())
                    ->where('status', WooSyncLog::STATUS_FAILED)
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
            ),
        ];
    }
}
