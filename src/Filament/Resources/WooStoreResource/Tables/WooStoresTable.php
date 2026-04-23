<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Madbox99\FilamentWooCommerce\Jobs\SyncStoreJob;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Services\WooCommerceClient;

final class WooStoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label(__('URL'))
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('last_sync_at')
                    ->label(__('Last sync'))
                    ->dateTime()
                    ->since()
                    ->placeholder(__('Never'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('test')
                    ->label(__('Test'))
                    ->icon(Heroicon::OutlinedSignal)
                    ->action(function (WooStore $record): void {
                        $ok = (new WooCommerceClient($record))->testConnection();

                        Notification::make()
                            ->title($ok ? __('Connection OK') : __('Connection failed'))
                            ->status($ok ? 'success' : 'danger')
                            ->send();
                    }),
                Action::make('sync')
                    ->label(__('Sync now'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->action(function (WooStore $record): void {
                        SyncStoreJob::dispatch($record->id);

                        Notification::make()
                            ->title(__('Sync queued'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
