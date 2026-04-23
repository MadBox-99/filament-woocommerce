<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $woo_store_id
 * @property string $entity_type
 * @property int $external_id
 * @property string|null $mappable_type
 * @property int|null $mappable_id
 * @property array<string, mixed>|null $payload
 * @property string|null $remote_hash
 * @property Carbon|null $last_synced_at
 */
final class WooMapping extends Model
{
    use HasFactory;

    protected $table = 'woo_mappings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'external_id' => 'integer',
            'payload' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(WooStore::class, 'woo_store_id');
    }

    public function mappable(): MorphTo
    {
        return $this->morphTo();
    }
}
