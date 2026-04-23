<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Madbox99\FilamentWooCommerce\Models\WooStore;

final class CustomerSyncer extends AbstractSyncer
{
    public function entityType(): string
    {
        return 'customers';
    }

    protected function remoteEndpoint(): string
    {
        return 'customers';
    }

    protected function remoteQuery(WooStore $store): array
    {
        return [
            'orderby' => 'registered_date',
            'order' => 'desc',
        ];
    }

    /**
     * Enrich the WooCommerce payload before the generic field_map runs so
     * billing fallbacks and a synthetic `full_name` become available to any
     * host-column the user maps them to. We never inject output columns —
     * the final shape is always controlled by config field_map.
     */
    protected function transform(array $payload, WooStore $store): array
    {
        $billing = (array) ($payload['billing'] ?? []);

        foreach (['first_name', 'last_name', 'phone', 'email'] as $key) {
            if (empty($payload[$key]) && ! empty($billing[$key])) {
                $payload[$key] = $billing[$key];
            }
        }

        $fullName = trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? ''));
        if ($fullName !== '') {
            $payload['full_name'] = $fullName;
        }

        return parent::transform($payload, $store);
    }
}
