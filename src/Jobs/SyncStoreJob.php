<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Sync\SyncRegistry;

/**
 * Dispatches a chain of per-entity sync jobs for a single store, in the
 * correct dependency order (categories → products → customers → orders).
 */
final class SyncStoreJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $storeId)
    {
        $this->onConnection(config('filament-woocommerce.queue.connection'));
        $this->onQueue(config('filament-woocommerce.queue.name', 'default'));
    }

    public function handle(SyncRegistry $registry): void
    {
        $store = WooStore::query()->find($this->storeId);
        if ($store === null || ! $store->is_active) {
            return;
        }

        $jobs = collect($registry->enabledEntities())
            ->map(fn (string $entity): SyncEntityJob => new SyncEntityJob($store->id, $entity))
            ->all();

        if ($jobs === []) {
            return;
        }

        Bus::chain($jobs)
            ->onQueue((string) config('filament-woocommerce.queue.name', 'default'))
            ->dispatch();

        $store->update(['last_sync_at' => now()]);
    }
}
