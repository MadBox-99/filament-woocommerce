<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class WooStoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Connection'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label(__('Store URL'))
                            ->url()
                            ->required()
                            ->placeholder('https://example.com')
                            ->helperText(__('Root URL of the WordPress site. Do not include /wp-json.')),
                        TextInput::make('consumer_key')
                            ->label(__('Consumer key'))
                            ->required()
                            ->password()
                            ->revealable()
                            ->autocomplete(false),
                        TextInput::make('consumer_secret')
                            ->label(__('Consumer secret'))
                            ->required()
                            ->password()
                            ->revealable()
                            ->autocomplete(false),
                        TextInput::make('api_version')
                            ->label(__('API version'))
                            ->default('wc/v3')
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                        self::tenantField(),
                    ]),
            ]);
    }

    private static function tenantField(): Select|TextInput
    {
        $tenantColumn = config('filament-woocommerce.tenant.column');
        $tenantModel = config('filament-woocommerce.tenant.model');
        $labelColumn = (string) config('filament-woocommerce.tenant.label_column', 'name');

        if ($tenantColumn === null) {
            return TextInput::make('tenant_id')->hidden();
        }

        if ($tenantModel !== null && class_exists($tenantModel)) {
            return Select::make('tenant_id')
                ->label(__('Tenant'))
                ->options(fn (): array => $tenantModel::query()
                    ->pluck($labelColumn, 'id')
                    ->all())
                ->default(fn (): int|string|null => Filament::getTenant()?->getKey())
                ->searchable()
                ->required();
        }

        return TextInput::make('tenant_id')
            ->label(__('Tenant ID'))
            ->numeric()
            ->default(fn (): int|string|null => Filament::getTenant()?->getKey())
            ->required();
    }
}
