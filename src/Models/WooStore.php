<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $consumer_key
 * @property string $consumer_secret
 * @property string $api_version
 * @property bool $is_active
 * @property int|null $tenant_id
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $last_sync_at
 */
final class WooStore extends Model
{
    use HasFactory;

    protected $table = 'woo_stores';

    protected $guarded = ['id'];

    protected $attributes = [
        'api_version' => 'wc/v3',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tenant_id' => 'integer',
            'settings' => 'array',
            'consumer_key' => 'encrypted',
            'consumer_secret' => 'encrypted',
            'last_sync_at' => 'datetime',
        ];
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(WooMapping::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(WooSyncLog::class);
    }

    protected function baseUrl(): Attribute
    {
        return Attribute::get(fn (): string => rtrim($this->url, '/').'/wp-json/'.$this->api_version);
    }
}
