<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Filament\Resources\WooSyncLogResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;

final class WooSyncLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('store.name')
                    ->label(__('Store'))
                    ->sortable(),
                TextColumn::make('entity_type')
                    ->label(__('Entity'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        WooSyncLog::STATUS_SUCCESS => 'success',
                        WooSyncLog::STATUS_FAILED => 'danger',
                        WooSyncLog::STATUS_PARTIAL => 'warning',
                        WooSyncLog::STATUS_RUNNING => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('processed_count')
                    ->label(__('Processed'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('failed_count')
                    ->label(__('Failed'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label(__('Started'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label(__('Finished'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('entity_type')
                    ->label(__('Entity'))
                    ->options([
                        'product_categories' => __('Categories'),
                        'products' => __('Products'),
                        'customers' => __('Customers'),
                        'orders' => __('Orders'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        WooSyncLog::STATUS_PENDING => __('Pending'),
                        WooSyncLog::STATUS_RUNNING => __('Running'),
                        WooSyncLog::STATUS_SUCCESS => __('Success'),
                        WooSyncLog::STATUS_PARTIAL => __('Partial'),
                        WooSyncLog::STATUS_FAILED => __('Failed'),
                    ]),
            ]);
    }
}
