<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Sync\CategorySyncer;
use Madbox99\FilamentWooCommerce\Sync\ProductSyncer;
use Madbox99\FilamentWooCommerce\Sync\SyncRegistry;

it('resolves configured syncers', function (): void {
    $registry = app(SyncRegistry::class);

    expect($registry->for('products'))->toBeInstanceOf(ProductSyncer::class);
    expect($registry->for('product_categories'))->toBeInstanceOf(CategorySyncer::class);
});

it('returns enabled entities in dependency order', function (): void {
    $registry = app(SyncRegistry::class);

    expect($registry->enabledEntities())
        ->toBe(['product_categories', 'products', 'customers', 'orders']);
});

it('skips entities marked disabled in config', function (): void {
    config()->set('filament-woocommerce.mappings.orders.enabled', false);

    expect(app(SyncRegistry::class)->enabledEntities())
        ->toBe(['product_categories', 'products', 'customers']);
});
