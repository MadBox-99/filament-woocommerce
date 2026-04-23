<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Madbox99\FilamentWooCommerce\Models\WooMapping;

/**
 * Add this trait to any host-app model (Product, Customer, Order, …) you want
 * to link to WooCommerce records. Registers a polymorphic relation to
 * `woo_mappings` so you can look up the remote ID without adding columns to
 * the host schema.
 */
trait HasWooMapping
{
    public function wooMappings(): MorphMany
    {
        return $this->morphMany(WooMapping::class, 'mappable');
    }

    public function wooMappingFor(int $storeId): ?WooMapping
    {
        return $this->wooMappings()
            ->where('woo_store_id', $storeId)
            ->first();
    }

    public function wooExternalId(int $storeId): ?int
    {
        return $this->wooMappingFor($storeId)?->external_id;
    }

    public function scopeWhereWooExternalId($query, int $storeId, int $externalId, string $entityType)
    {
        return $query->whereHas('wooMappings', function ($q) use ($storeId, $externalId, $entityType): void {
            $q->where('woo_store_id', $storeId)
                ->where('entity_type', $entityType)
                ->where('external_id', $externalId);
        });
    }
}
