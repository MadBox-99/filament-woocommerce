<?php

declare(strict_types=1);

use Madbox99\FilamentWooCommerce\Sync\CategorySyncer;
use Madbox99\FilamentWooCommerce\Sync\CustomerSyncer;
use Madbox99\FilamentWooCommerce\Sync\OrderSyncer;
use Madbox99\FilamentWooCommerce\Sync\ProductSyncer;

return [
    /*
    |--------------------------------------------------------------------------
    | Entity Mappings
    |--------------------------------------------------------------------------
    |
    | Each entity the plugin can sync maps to one local Eloquent model in the
    | host application. Override `model` with your own model class and tweak
    | `field_map` to translate WooCommerce payload keys to your columns.
    |
    | Setting `enabled` to false skips the entity entirely (no UI, no jobs).
    |
    */

    'mappings' => [
        'products' => [
            'enabled' => true,
            'model' => null, // e.g. \App\Models\Product::class
            'syncer' => ProductSyncer::class,
            'field_map' => [
                'name' => 'name',
                'sku' => 'sku',
                'description' => 'description',
                'short_description' => 'short_description',
                'regular_price' => 'unit_price',
                'stock_quantity' => 'stock_quantity',
                'status' => 'status',
            ],
        ],

        'product_categories' => [
            'enabled' => true,
            'model' => null, // e.g. \App\Models\ProductCategory::class
            'syncer' => CategorySyncer::class,
            'field_map' => [
                'name' => 'name',
                'slug' => 'slug',
                'description' => 'description',
            ],
        ],

        'customers' => [
            'enabled' => true,
            'model' => null, // e.g. \App\Models\Customer::class
            'syncer' => CustomerSyncer::class,
            'field_map' => [
                'email' => 'email',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'username' => 'username',
            ],
        ],

        'orders' => [
            'enabled' => true,
            'model' => null, // e.g. \App\Models\Order::class
            'syncer' => OrderSyncer::class,
            'field_map' => [
                'number' => 'external_number',
                'status' => 'status',
                'total' => 'total',
                'currency' => 'currency',
                'date_created' => 'ordered_at',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenant support
    |--------------------------------------------------------------------------
    |
    | When the host application scopes its models by a tenant (team, account,
    | organization, …), each WooStore can be assigned a tenant_id. The syncer
    | then auto-populates the configured column on newly created records and
    | bypasses global scopes when resolving existing ones — so sync jobs work
    | outside of an authenticated tenant context.
    |
    | - column: the column name on host models (e.g. 'team_id'). Null disables
    |           the feature entirely, leaving v0.1 behavior intact.
    | - model:  optional Eloquent model used to render a Select in the store
    |           form, letting admins pick a tenant. Null falls back to a plain
    |           numeric input.
    | - label_column: column on the tenant model to display in the Select.
    | - bypass_global_scopes: when true, mapping lookups skip global scopes so
    |           BelongsToTeam-style traits don't hide rows from queue workers.
    | - allow_selection: who may choose the tenant in the store form. Accepts a
    |           bool, or a class-string of an invokable resolved from the
    |           container returning bool (config-cache safe — avoid closures).
    |           When it resolves to false the field is disabled and forced to
    |           the current panel tenant, so regular users can't reassign a
    |           store to another team. Defaults to true.
    |
    */

    'tenant' => [
        'column' => env('WOO_TENANT_COLUMN'),
        'model' => null,
        'label_column' => 'name',
        'bypass_global_scopes' => true,
        'allow_selection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'connection' => env('WOO_QUEUE_CONNECTION'),
        'name' => env('WOO_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler
    |--------------------------------------------------------------------------
    |
    | When enabled, the plugin registers a scheduled job that syncs every
    | active store at the given cron expression. Disable if you prefer to
    | trigger sync manually or via webhooks only.
    |
    */

    'schedule' => [
        'enabled' => env('WOO_SCHEDULE_ENABLED', true),
        'cron' => env('WOO_SCHEDULE_CRON', '0 * * * *'), // hourly
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => 30,
        'per_page' => 100,
        'verify_ssl' => env('WOO_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'enabled' => env('WOO_WEBHOOKS_ENABLED', false),
        'secret_header' => 'x-wc-webhook-signature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Panel
    |--------------------------------------------------------------------------
    */

    'filament' => [
        'navigation_group' => 'WooCommerce',
        'navigation_sort' => 90,
        'cluster' => null,
    ],
];
