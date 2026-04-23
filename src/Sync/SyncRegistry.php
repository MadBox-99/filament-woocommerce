<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Madbox99\FilamentWooCommerce\Sync\Contracts\EntitySyncer;

/**
 * Resolves a syncer for a given entity type based on the plugin config. The
 * order here is significant: categories before products (products reference
 * category mappings), customers before orders (orders reference customer
 * mappings).
 */
final class SyncRegistry
{
    public const ENTITY_ORDER = [
        'product_categories',
        'products',
        'customers',
        'orders',
    ];

    public function for(string $entityType): ?EntitySyncer
    {
        $class = config('filament-woocommerce.mappings.'.$entityType.'.syncer');
        if ($class === null || ! class_exists($class)) {
            return null;
        }

        $instance = app($class);

        return $instance instanceof EntitySyncer ? $instance : null;
    }

    /** @return array<int, string> */
    public function enabledEntities(): array
    {
        return array_values(array_filter(
            self::ENTITY_ORDER,
            fn (string $entity): bool => (bool) config('filament-woocommerce.mappings.'.$entity.'.enabled', false),
        ));
    }
}
