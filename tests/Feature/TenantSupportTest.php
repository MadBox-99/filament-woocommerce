<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Sync\ProductSyncer;
use Madbox99\FilamentWooCommerce\Tests\Fixtures\TenantProduct;

beforeEach(function (): void {
    Schema::create('tenant_products', function (Blueprint $t): void {
        $t->id();
        $t->unsignedBigInteger('team_id')->nullable();
        $t->string('name')->nullable();
        $t->string('sku')->nullable();
        $t->timestamps();
    });

    TenantProduct::$scopeTeamId = false;
    TenantProduct::$currentTeamId = 0;

    config()->set('filament-woocommerce.mappings.products.model', TenantProduct::class);
    config()->set('filament-woocommerce.mappings.products.field_map', [
        'name' => 'name',
        'sku' => 'sku',
    ]);

    $this->store = WooStore::query()->create([
        'name' => 'Tenant Store',
        'url' => 'https://shop.test',
        'consumer_key' => 'ck',
        'consumer_secret' => 'cs',
        'tenant_id' => 42,
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('tenant_products');
});

it('auto-populates the tenant column on newly created records', function (): void {
    config()->set('filament-woocommerce.tenant.column', 'team_id');

    (new ProductSyncer)->syncOne($this->store, [
        'id' => 101,
        'name' => 'Widget',
        'sku' => 'WDG-1',
    ]);

    $product = TenantProduct::query()->first();

    expect($product)->not->toBeNull();
    expect($product->team_id)->toBe(42);
    expect($product->name)->toBe('Widget');
});

it('leaves the tenant column untouched when the feature is disabled', function (): void {
    config()->set('filament-woocommerce.tenant.column', null);

    (new ProductSyncer)->syncOne($this->store, [
        'id' => 102,
        'name' => 'No-tenant widget',
    ]);

    $product = TenantProduct::query()->first();

    expect($product)->not->toBeNull();
    expect($product->team_id)->toBeNull();
});

it('bypasses global scopes when resolving an existing mapping', function (): void {
    config()->set('filament-woocommerce.tenant.column', 'team_id');
    config()->set('filament-woocommerce.tenant.bypass_global_scopes', true);

    $product = TenantProduct::query()->create([
        'team_id' => 42,
        'name' => 'Original',
        'sku' => 'ORIG',
    ]);

    WooMapping::query()->create([
        'woo_store_id' => $this->store->id,
        'entity_type' => 'products',
        'external_id' => 200,
        'mappable_type' => $product->getMorphClass(),
        'mappable_id' => $product->id,
    ]);

    TenantProduct::$scopeTeamId = true;
    TenantProduct::$currentTeamId = 999; // no tenant context → would hide the row

    (new ProductSyncer)->syncOne($this->store, [
        'id' => 200,
        'name' => 'Renamed',
        'sku' => 'ORIG',
    ]);

    TenantProduct::$scopeTeamId = false;

    expect($product->fresh()->name)->toBe('Renamed');
    expect(TenantProduct::query()->count())->toBe(1);
});

it('does not override tenant column on pre-existing records', function (): void {
    config()->set('filament-woocommerce.tenant.column', 'team_id');

    $product = TenantProduct::query()->create([
        'team_id' => 7, // different tenant already on record
        'name' => 'Pre-existing',
    ]);

    WooMapping::query()->create([
        'woo_store_id' => $this->store->id,
        'entity_type' => 'products',
        'external_id' => 300,
        'mappable_type' => $product->getMorphClass(),
        'mappable_id' => $product->id,
    ]);

    (new ProductSyncer)->syncOne($this->store, [
        'id' => 300,
        'name' => 'Updated name',
    ]);

    expect($product->fresh()->team_id)->toBe(7);
    expect($product->fresh()->name)->toBe('Updated name');
});
