<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Illuminate\Database\Eloquent\Model;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;

final class OrderSyncer extends AbstractSyncer
{
    public function entityType(): string
    {
        return 'orders';
    }

    protected function remoteEndpoint(): string
    {
        return 'orders';
    }

    protected function remoteQuery(WooStore $store): array
    {
        return [
            'orderby' => 'date',
            'order' => 'desc',
        ];
    }

    protected function beforeFill(Model $local, array $payload, WooStore $store): void
    {
        $customerExternalId = (int) ($payload['customer_id'] ?? 0);
        if ($customerExternalId === 0) {
            return;
        }

        $mapping = WooMapping::query()
            ->where('woo_store_id', $store->id)
            ->where('entity_type', 'customers')
            ->where('external_id', $customerExternalId)
            ->first();

        if ($mapping?->mappable_id && isset($local->customer_id)) {
            $local->customer_id = $mapping->mappable_id;
        }
    }
}
