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
