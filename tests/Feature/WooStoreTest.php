<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Models\WooStore;

it('creates a store and encrypts credentials at rest', function (): void {
    $store = WooStore::query()->create([
        'name' => 'Test Store',
        'url' => 'https://example.com',
        'consumer_key' => 'ck_secret',
        'consumer_secret' => 'cs_secret',
    ]);

    expect($store->consumer_key)->toBe('ck_secret');

    $raw = DB::table('woo_stores')->where('id', $store->id)->value('consumer_key');
    expect($raw)->not->toBe('ck_secret');
});

it('builds the correct base URL from the store URL', function (): void {
    $store = new WooStore([
        'url' => 'https://shop.example.com/',
        'api_version' => 'wc/v3',
    ]);

    expect($store->base_url)->toBe('https://shop.example.com/wp-json/wc/v3');
});
