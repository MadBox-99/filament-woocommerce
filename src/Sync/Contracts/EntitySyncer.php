<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync\Contracts;

use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;

interface EntitySyncer
{
    /** The config key this syncer handles (e.g. `products`). */
    public function entityType(): string;

    /** Pull all matching records from the remote store and persist them locally. */
    public function sync(WooStore $store, WooSyncLog $log): void;

    /**
     * Upsert a single remote payload into the local model. Returns the
     * persisted local model (or null if skipped).
     *
     * @param  array<string, mixed>  $payload
     */
    public function syncOne(WooStore $store, array $payload): ?object;
}
