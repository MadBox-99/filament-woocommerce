<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\WooStoreResource;

final class EditWooStore extends EditRecord
{
    protected static string $resource = WooStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
