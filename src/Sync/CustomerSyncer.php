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

    protected function transform(array $payload, WooStore $store): array
    {
        $data = parent::transform($payload, $store);

        $billing = (array) ($payload['billing'] ?? []);
        if (! isset($data['first_name']) && isset($billing['first_name'])) {
            $data['first_name'] = $billing['first_name'];
        }
        if (! isset($data['last_name']) && isset($billing['last_name'])) {
            $data['last_name'] = $billing['last_name'];
        }
        if (! isset($data['phone']) && isset($billing['phone'])) {
            $data['phone'] = $billing['phone'];
        }

        return $data;
    }
}
