<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\Pages\ListWooSyncLogs;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\Tables\WooSyncLogsTable;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;

final class WooSyncLogResource extends Resource
{
    protected static ?string $model = WooSyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    /**
     * Sync logs belong to a WooStore (not directly to a tenant) and the plugin
     * filters by store explicitly, so Filament's auto tenant scoping would
     * raise a LogicException when the host panel enables `->tenant()`.
     */
    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return (string) config('filament-woocommerce.filament.navigation_group', 'WooCommerce');
    }

    public static function getNavigationSort(): ?int
    {
        return (int) config('filament-woocommerce.filament.navigation_sort', 90) + 1;
    }

    public static function getNavigationLabel(): string
    {
        return __('Sync logs');
    }

    public static function getModelLabel(): string
    {
        return __('Sync log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sync logs');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return WooSyncLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWooSyncLogs::route('/'),
        ];
    }
}
