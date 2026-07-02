<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Filament\Resources\WooStoreResource\Schemas\WooStoreForm;
use Madbox99\FilamentWooCommerce\Tests\Fixtures\DenyTenantSelection;

it('allows tenant selection by default', function (): void {
    config()->set('filament-woocommerce.tenant.allow_selection', null);

    expect(WooStoreForm::canSelectTenant())->toBeTrue();
});

it('respects a boolean allow_selection flag', function (): void {
    config()->set('filament-woocommerce.tenant.allow_selection', false);

    expect(WooStoreForm::canSelectTenant())->toBeFalse();
});

it('resolves an invokable class-string from the container', function (): void {
    config()->set('filament-woocommerce.tenant.allow_selection', DenyTenantSelection::class);

    expect(WooStoreForm::canSelectTenant())->toBeFalse();
});
