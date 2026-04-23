<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Madbox99\FilamentWooCommerce\Concerns\HasWooMapping;

/**
 * Fake host-app model used in tenant-support tests. Stands in for the CRM's
 * Product/Customer model that uses a team_id column and a BelongsToTeam-style
 * global scope.
 */
final class TenantProduct extends Model
{
    use HasWooMapping;

    protected $table = 'tenant_products';

    protected $guarded = ['id'];

    public static bool $scopeTeamId = true;

    public static int $currentTeamId = 0;

    protected static function booted(): void
    {
        self::addGlobalScope(new class implements Scope
        {
            public function apply($builder, $model): void
            {
                if (! TenantProduct::$scopeTeamId) {
                    return;
                }

                $builder->where('team_id', TenantProduct::$currentTeamId);
            }
        });
    }
}
