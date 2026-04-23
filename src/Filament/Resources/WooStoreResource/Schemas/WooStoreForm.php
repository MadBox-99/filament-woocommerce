<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Schemas;

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
                    ]),
            ]);
    }
}
