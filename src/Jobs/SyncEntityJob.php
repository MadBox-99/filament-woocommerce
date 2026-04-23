<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;
use Madbox99\FilamentWooCommerce\Sync\SyncRegistry;
use Throwable;

final class SyncEntityJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public int $tries = 3;

    public function __construct(
        public int $storeId,
        public string $entityType,
    ) {
        $this->onConnection(config('filament-woocommerce.queue.connection'));
        $this->onQueue(config('filament-woocommerce.queue.name', 'default'));
    }

    public function handle(SyncRegistry $registry): void
    {
        $store = WooStore::query()->find($this->storeId);
        if ($store === null || ! $store->is_active) {
            return;
        }

        $syncer = $registry->for($this->entityType);
        if ($syncer === null) {
            return;
        }

        $log = WooSyncLog::query()->create([
            'woo_store_id' => $store->id,
            'entity_type' => $this->entityType,
            'status' => WooSyncLog::STATUS_PENDING,
        ]);

        $log->markRunning();

        try {
            $syncer->sync($store, $log);
            $log->refresh()->markSuccess();
        } catch (Throwable $e) {
            $log->markFailed($e->getMessage());
            report($e);

            throw $e;
        }
    }
}
