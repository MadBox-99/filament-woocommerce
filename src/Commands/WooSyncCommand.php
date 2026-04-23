<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Commands;

use Illuminate\Console\Command;
use Madbox99\FilamentWooCommerce\Jobs\SyncEntityJob;
use Madbox99\FilamentWooCommerce\Jobs\SyncStoreJob;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Sync\SyncRegistry;

final class WooSyncCommand extends Command
{
    protected $signature = 'woo:sync
        {--store= : Limit sync to a single store ID (default: all active stores)}
        {--entity= : Limit sync to a single entity (products, product_categories, customers, orders)}
        {--sync : Run inline instead of dispatching to the queue}';

    protected $description = 'Sync records from WooCommerce stores into the host application.';

    public function handle(SyncRegistry $registry): int
    {
        $stores = WooStore::query()
            ->when($this->option('store'), fn ($q, $id) => $q->whereKey($id))
            ->where('is_active', true)
            ->get();

        if ($stores->isEmpty()) {
            $this->warn('No active stores found.');

            return self::SUCCESS;
        }

        $entity = $this->option('entity');
        $inline = (bool) $this->option('sync');

        foreach ($stores as $store) {
            $this->info("Store: {$store->name} (#{$store->id})");

            if ($entity !== null) {
                $this->dispatchEntity($store->id, $entity, $inline);

                continue;
            }

            if ($inline) {
                foreach ($registry->enabledEntities() as $enabledEntity) {
                    $this->dispatchEntity($store->id, $enabledEntity, true);
                }
            } else {
                SyncStoreJob::dispatch($store->id);
                $this->line('  → queued SyncStoreJob');
            }
        }

        return self::SUCCESS;
    }

    private function dispatchEntity(int $storeId, string $entity, bool $inline): void
    {
        if ($inline) {
            (new SyncEntityJob($storeId, $entity))->handle(app(SyncRegistry::class));
            $this->line("  → synced {$entity}");
        } else {
            SyncEntityJob::dispatch($storeId, $entity);
            $this->line("  → queued {$entity}");
        }
    }
}
