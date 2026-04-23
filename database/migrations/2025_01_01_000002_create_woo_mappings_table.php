<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('woo_store_id')->constrained('woo_stores')->cascadeOnDelete();
            $table->string('entity_type'); // products, product_categories, customers, orders
            $table->unsignedBigInteger('external_id');
            $table->nullableMorphs('mappable');
            $table->json('payload')->nullable(); // last known remote payload (for diffing)
            $table->string('remote_hash', 64)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['woo_store_id', 'entity_type', 'external_id'], 'woo_mappings_store_entity_ext_unique');
            $table->index(['entity_type', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_mappings');
    }
};
