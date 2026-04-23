<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\WooStoreResource;

final class CreateWooStore extends CreateRecord
{
    protected static string $resource = WooStoreResource::class;
}
