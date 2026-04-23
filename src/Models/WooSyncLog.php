<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $woo_store_id
 * @property string $entity_type
 * @property string $status
 * @property int $processed_count
 * @property int $created_count
 * @property int $updated_count
 * @property int $failed_count
 * @property string|null $error
 * @property array<string, mixed>|null $context
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 */
final class WooSyncLog extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PARTIAL = 'partial';

    protected $table = 'woo_sync_logs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(WooStore::class, 'woo_store_id');
    }

    public function markRunning(): self
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        return $this;
    }

    public function markSuccess(): self
    {
        $this->update([
            'status' => $this->failed_count > 0 ? self::STATUS_PARTIAL : self::STATUS_SUCCESS,
            'finished_at' => now(),
        ]);

        return $this;
    }

    public function markFailed(string $error): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error' => $error,
            'finished_at' => now(),
        ]);

        return $this;
    }
}
