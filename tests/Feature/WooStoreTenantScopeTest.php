<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;

/**
 * Creates a store belonging to the given tenant (or none when null).
 */
function makeTenantStore(?int $tenantId): WooStore
{
    return WooStore::query()->create([
        'name' => 'Store '.($tenantId ?? 'none'),
        'url' => 'https://shop-'.($tenantId ?? 'x').'.test',
        'consumer_key' => 'ck',
        'consumer_secret' => 'cs',
        'tenant_id' => $tenantId,
    ]);
}

it('filters stores to a single tenant', function (): void {
    makeTenantStore(1);
    makeTenantStore(1);
    makeTenantStore(2);
    makeTenantStore(null);

    expect(WooStore::query()->forTenant(1)->count())->toBe(2);
    expect(WooStore::query()->forTenant(2)->count())->toBe(1);
});

it('returns every store when the tenant id is null', function (): void {
    makeTenantStore(1);
    makeTenantStore(2);
    makeTenantStore(null);

    expect(WooStore::query()->forTenant(null)->count())->toBe(3);
});

it('scopes related records to a tenant through the store relationship', function (): void {
    $mine = makeTenantStore(1);
    $theirs = makeTenantStore(2);

    WooMapping::query()->create([
        'woo_store_id' => $mine->id,
        'entity_type' => 'products',
        'external_id' => 1,
    ]);
    WooMapping::query()->create([
        'woo_store_id' => $theirs->id,
        'entity_type' => 'products',
        'external_id' => 2,
    ]);

    $count = WooMapping::query()
        ->whereHas('store', fn ($query) => $query->forTenant(1))
        ->count();

    expect($count)->toBe(1);
});
