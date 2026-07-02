<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Tests\Fixtures;

/**
 * Invokable gate fixture used to prove config-driven tenant selection gating.
 */
final class DenyTenantSelection
{
    public function __invoke(): bool
    {
        return false;
    }
}
