# Filament WooCommerce

WooCommerce integration plugin for Filament v5. Pulls **products + categories**, **customers**, and **orders** from one or more WooCommerce stores into any Laravel/Filament CRM via **configurable model mapping**.

- Laravel 11 / 12 / 13
- Filament v4 / v5
- PHP 8.3+
- One-way sync: WooCommerce → host app
- Multi-store: manage several WooCommerce stores from one Filament panel

## Installation

```bash
composer require madbox-99/filament-woocommerce
php artisan vendor:publish --tag=filament-woocommerce-config
php artisan vendor:publish --tag=filament-woocommerce-migrations
php artisan migrate
```

Register the plugin on your Filament panel:

```php
// app/Providers/Filament/AdminPanelProvider.php
use Madbox99\FilamentWooCommerce\FilamentWooCommercePlugin;

$panel->plugins([
    FilamentWooCommercePlugin::make(),
]);
```

## Configuration

Edit `config/filament-woocommerce.php` and point each entity at a host-app model:

```php
'mappings' => [
    'products' => [
        'enabled' => true,
        'model' => \App\Models\Product::class,
        'syncer' => \Madbox99\FilamentWooCommerce\Sync\ProductSyncer::class,
        'field_map' => [
            'name' => 'name',
            'sku' => 'sku',
            'regular_price' => 'unit_price',
            // ...
        ],
    ],
    // product_categories, customers, orders …
],
```

Add the `HasWooMapping` trait to each mapped model:

```php
use Madbox99\FilamentWooCommerce\Concerns\HasWooMapping;

final class Product extends Model
{
    use HasWooMapping;
}
```

`HasWooMapping` adds a polymorphic relation to the `woo_mappings` table — no extra columns required on your own models.

## Usage

### Add a store

Go to **WooCommerce → Stores** in your Filament panel and create a new store with the site URL, consumer key, and consumer secret from **WP Admin → WooCommerce → Settings → Advanced → REST API**.

Click **Test** to verify the connection.

### Sync

From the panel: click **Sync now** on a store row.

From the CLI:

```bash
php artisan woo:sync                         # queue all active stores
php artisan woo:sync --store=1               # queue a single store
php artisan woo:sync --store=1 --entity=products
php artisan woo:sync --store=1 --sync        # run inline (no queue)
```

The scheduler can run sync automatically — enable it in config (on by default, hourly).

### Lookups

```php
$product = Product::find(42);
$externalId = $product->wooExternalId(storeId: 1);
$mapping = $product->wooMappingFor(storeId: 1);
```

## How mapping works

1. Each remote record has a unique `external_id` from WooCommerce.
2. When the syncer persists a local record, it creates/updates a row in `woo_mappings` linking the remote ID ↔ the local model (polymorphic).
3. Re-syncing re-uses the existing mapping, so records are **updated** in place rather than duplicated.
4. Cross-entity links (e.g. order → customer, product → category) resolve via mapping lookups at sync time.

## Testing

```bash
composer test
```

## License

MIT
