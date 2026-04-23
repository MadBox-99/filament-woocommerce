<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Services\WooCommerceClient;

beforeEach(function (): void {
    $this->store = WooStore::query()->create([
        'name' => 'Test',
        'url' => 'https://shop.test',
        'consumer_key' => 'ck_x',
        'consumer_secret' => 'cs_x',
    ]);
});

it('paginates across X-WP-TotalPages', function (): void {
    Http::fake([
        'shop.test/*' => Http::sequence()
            ->push([['id' => 1], ['id' => 2]], 200, ['X-WP-TotalPages' => '2'])
            ->push([['id' => 3]], 200, ['X-WP-TotalPages' => '2']),
    ]);

    $client = new WooCommerceClient($this->store);
    $items = iterator_to_array($client->paginate('products'), false);

    expect($items)->toHaveCount(3);
    expect(array_column($items, 'id'))->toBe([1, 2, 3]);
});

it('stops paginating when the response is empty', function (): void {
    Http::fake([
        'shop.test/*' => Http::response([], 200, ['X-WP-TotalPages' => '5']),
    ]);

    $client = new WooCommerceClient($this->store);
    $items = iterator_to_array($client->paginate('products'), false);

    expect($items)->toBeEmpty();
});

it('reports a failed connection without throwing', function (): void {
    Http::fake([
        '*' => Http::response([], 500),
    ]);

    $client = new WooCommerceClient($this->store);

    expect($client->testConnection())->toBeFalse();
});
