<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages\CreateWooStore;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages\EditWooStore;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages\ListWooStores;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Schemas\WooStoreForm;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Tables\WooStoresTable;
use Madbox99\FilamentWooCommerce\Models\WooStore;

final class WooStoreResource extends Resource
{
    protected static ?string $model = WooStore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    public static function getNavigationGroup(): ?string
    {
        return (string) config('filament-woocommerce.filament.navigation_group', 'WooCommerce');
    }

    public static function getNavigationSort(): ?int
    {
        return (int) config('filament-woocommerce.filament.navigation_sort', 90);
    }

    public static function getNavigationLabel(): string
    {
        return __('Stores');
    }

    public static function getModelLabel(): string
    {
        return __('WooCommerce store');
    }

    public static function getPluralModelLabel(): string
    {
        return __('WooCommerce stores');
    }

    public static function form(Schema $schema): Schema
    {
        return WooStoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WooStoresTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWooStores::route('/'),
            'create' => CreateWooStore::route('/create'),
            'edit' => EditWooStore::route('/{record}/edit'),
        ];
    }
}
