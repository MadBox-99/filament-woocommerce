<?php

declare(strict_types=1);

namespace Madbox99\FilamentWooCommerce\Services;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Madbox99\FilamentWooCommerce\Models\WooStore;

/**
 * Thin wrapper around the WooCommerce REST API (v3) using Laravel's HTTP
 * client. Uses Basic Auth with consumer key/secret over HTTPS — for
 * HTTP-only dev stores, WooCommerce falls back to OAuth1 but that path
 * is out of scope here.
 */
final class WooCommerceClient
{
    public function __construct(public readonly WooStore $store) {}

    public function http(): PendingRequest
    {
        $config = config('filament-woocommerce.http');

        return Http::baseUrl($this->store->base_url)
            ->withBasicAuth($this->store->consumer_key, $this->store->consumer_secret)
            ->acceptJson()
            ->timeout((int) ($config['timeout'] ?? 30))
            ->withOptions(['verify' => (bool) ($config['verify_ssl'] ?? true)]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     *
     * @throws RequestException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->http()->get(ltrim($endpoint, '/'), $query)
            ->throw()
            ->json() ?? [];
    }

    /**
     * Stream every record of a paginated WooCommerce endpoint, yielding one
     * item at a time. Uses the per_page config and the X-WP-TotalPages header
     * to iterate until exhausted.
     *
     * @param  array<string, mixed>  $query
     * @return Generator<int, array<string, mixed>>
     *
     * @throws RequestException
     */
    public function paginate(string $endpoint, array $query = []): Generator
    {
        $perPage = (int) (config('filament-woocommerce.http.per_page') ?? 100);
        $page = 1;

        do {
            $response = $this->http()
                ->get(ltrim($endpoint, '/'), array_merge($query, [
                    'per_page' => $perPage,
                    'page' => $page,
                ]))
                ->throw();

            $items = $response->json();
            if (! is_array($items) || $items === []) {
                return;
            }

            foreach ($items as $item) {
                yield $item;
            }

            $totalPages = (int) ($response->header('X-WP-TotalPages') ?: 0);
            $page++;
        } while ($totalPages > 0 && $page <= $totalPages);
    }

    public function testConnection(): bool
    {
        try {
            $this->http()->get('')->throw();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
