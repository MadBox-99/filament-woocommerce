<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\WooStoreResource;

final class ListWooStores extends ListRecords
{
    protected static string $resource = WooStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
