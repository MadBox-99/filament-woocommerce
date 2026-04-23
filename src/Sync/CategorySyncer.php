<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Madbox99\FilamentWooCommerce\Models\WooStore;

final class CategorySyncer extends AbstractSyncer
{
    public function entityType(): string
    {
        return 'product_categories';
    }

    protected function remoteEndpoint(): string
    {
        return 'products/categories';
    }

    protected function remoteQuery(WooStore $store): array
    {
        return [
            'orderby' => 'name',
            'order' => 'asc',
        ];
    }
}
