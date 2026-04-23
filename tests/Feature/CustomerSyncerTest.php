<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Sync\CustomerSyncer;

/**
 * @return array<string, mixed>
 */
function runTransform(array $payload, array $fieldMap = []): array
{
    config()->set('filament-woocommerce.mappings.customers.field_map', $fieldMap);

    $syncer = new CustomerSyncer;
    $reflection = new ReflectionMethod($syncer, 'transform');
    $reflection->setAccessible(true);

    return $reflection->invoke($syncer, $payload, new WooStore);
}

it('only emits columns listed in field_map — never hardcodes first_name or last_name', function (): void {
    $result = runTransform(
        ['id' => 1, 'email' => 'x@y.com', 'first_name' => 'John', 'last_name' => 'Doe'],
        ['email' => 'email'],
    );

    expect($result)->toHaveKey('email');
    expect($result)->not->toHaveKey('first_name');
    expect($result)->not->toHaveKey('last_name');
});

it('exposes a synthetic full_name built from first_name + last_name', function (): void {
    $result = runTransform(
        ['first_name' => 'Jane', 'last_name' => 'Smith'],
        ['full_name' => 'name'],
    );

    expect($result)->toBe(['name' => 'Jane Smith']);
});

it('falls back to billing fields when the top-level value is empty', function (): void {
    $result = runTransform(
        [
            'email' => '',
            'first_name' => '',
            'phone' => null,
            'billing' => [
                'email' => 'billing@y.com',
                'first_name' => 'Jane',
                'phone' => '+36123',
            ],
        ],
        [
            'email' => 'email',
            'phone' => 'phone',
            'full_name' => 'name',
        ],
    );

    expect($result)->toBe([
        'email' => 'billing@y.com',
        'phone' => '+36123',
        'name' => 'Jane',
    ]);
});

it('omits full_name when both first_name and last_name are empty', function (): void {
    $result = runTransform(
        ['email' => 'x@y.com'],
        ['full_name' => 'name', 'email' => 'email'],
    );

    expect($result)->toBe(['email' => 'x@y.com']);
});
