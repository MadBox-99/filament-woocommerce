<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Sync;

use Illuminate\Database\Eloquent\Model;
use Madbox99\FilamentWooCommerce\Models\WooMapping;
use Madbox99\FilamentWooCommerce\Models\WooStore;
use Madbox99\FilamentWooCommerce\Models\WooSyncLog;
use Madbox99\FilamentWooCommerce\Services\WooCommerceClient;
use Madbox99\FilamentWooCommerce\Sync\Contracts\EntitySyncer;
use Throwable;

abstract class AbstractSyncer implements EntitySyncer
{
    abstract public function entityType(): string;

    abstract protected function remoteEndpoint(): string;

    /** @return array<string, mixed> */
    abstract protected function remoteQuery(WooStore $store): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function transform(array $payload, WooStore $store): array
    {
        $fieldMap = $this->mappingConfig()['field_map'] ?? [];
        $data = [];

        foreach ($fieldMap as $remoteKey => $localColumn) {
            if (array_key_exists($remoteKey, $payload)) {
                $data[$localColumn] = $payload[$remoteKey];
            }
        }

        return $data;
    }

    public function sync(WooStore $store, WooSyncLog $log): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $client = new WooCommerceClient($store);

        foreach ($client->paginate($this->remoteEndpoint(), $this->remoteQuery($store)) as $payload) {
            try {
                $this->syncOne($store, $payload);
                $log->increment('processed_count');
            } catch (Throwable $e) {
                $log->increment('failed_count');
                report($e);
            }
        }
    }

    public function syncOne(WooStore $store, array $payload): ?object
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $modelClass = $this->mappingConfig()['model'] ?? null;
        if ($modelClass === null || ! class_exists($modelClass)) {
            return null;
        }

        $externalId = (int) ($payload['id'] ?? 0);
        if ($externalId === 0) {
            return null;
        }

        $mapping = WooMapping::query()
            ->where('woo_store_id', $store->id)
            ->where('entity_type', $this->entityType())
            ->where('external_id', $externalId)
            ->first();

        $attributes = $this->transform($payload, $store);
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        $local = $this->resolveLocal($mapping, $modelClass);

        if (! $local->exists) {
            $this->applyTenant($local, $store);
        }

        $this->beforeFill($local, $payload, $store);
        $local->fill($attributes);
        $local->save();

        $this->afterSave($local, $payload, $store);

        WooMapping::query()->updateOrCreate(
            [
                'woo_store_id' => $store->id,
                'entity_type' => $this->entityType(),
                'external_id' => $externalId,
            ],
            [
                'mappable_type' => $local->getMorphClass(),
                'mappable_id' => $local->getKey(),
                'payload' => $payload,
                'remote_hash' => $hash,
                'last_synced_at' => now(),
            ],
        );

        return $local;
    }

    protected function beforeFill(Model $local, array $payload, WooStore $store): void {}

    protected function afterSave(Model $local, array $payload, WooStore $store): void {}

    /**
     * Resolve the local Eloquent model for an existing mapping, optionally
     * bypassing global scopes so tenant-scoped traits don't hide rows when
     * sync runs outside of an authenticated tenant context.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function resolveLocal(?WooMapping $mapping, string $modelClass): Model
    {
        if ($mapping === null || $mapping->mappable_id === null) {
            return new $modelClass;
        }

        $query = $modelClass::query();
        if ((bool) config('filament-woocommerce.tenant.bypass_global_scopes', true)) {
            $query->withoutGlobalScopes();
        }

        return $query->find($mapping->mappable_id) ?: new $modelClass;
    }

    /**
     * Populate the tenant column on a newly-created local model when the
     * store has a tenant_id and a tenant.column is configured.
     */
    protected function applyTenant(Model $local, WooStore $store): void
    {
        $column = config('filament-woocommerce.tenant.column');
        if ($column === null || $store->tenant_id === null) {
            return;
        }

        $local->{$column} = $store->tenant_id;
    }

    protected function isEnabled(): bool
    {
        return (bool) ($this->mappingConfig()['enabled'] ?? false);
    }

    /** @return array<string, mixed> */
    protected function mappingConfig(): array
    {
        return (array) config('filament-woocommerce.mappings.'.$this->entityType(), []);
    }
}
