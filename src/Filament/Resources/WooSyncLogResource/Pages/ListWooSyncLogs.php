<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\WooSyncLogResource;

final class ListWooSyncLogs extends ListRecords
{
    protected static string $resource = WooSyncLogResource::class;
}
