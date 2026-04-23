<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Illuminate\Database\Eloquent\Model;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;

final class ProductSyncer extends AbstractSyncer
{
    public function entityType(): string
    {
        return 'products';
    }

    protected function remoteEndpoint(): string
    {
        return 'products';
    }

    protected function remoteQuery(WooStore $store): array
    {
        return [
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'desc',
        ];
    }

    protected function afterSave(Model $local, array $payload, WooStore $store): void
    {
        $categoryIds = [];
        foreach ((array) ($payload['categories'] ?? []) as $cat) {
            $externalCatId = (int) ($cat['id'] ?? 0);
            if ($externalCatId === 0) {
                continue;
            }

            $mapping = WooMapping::query()
                ->where('woo_store_id', $store->id)
                ->where('entity_type', 'product_categories')
                ->where('external_id', $externalCatId)
                ->first();

            if ($mapping?->mappable_id) {
                $categoryIds[] = $mapping->mappable_id;
            }
        }

        if ($categoryIds !== [] && method_exists($local, 'categories')) {
            $local->categories()->sync($categoryIds);
        } elseif ($categoryIds !== [] && isset($local->category_id)) {
            $local->forceFill(['category_id' => $categoryIds[0]])->save();
        }
    }
}
