<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_stores', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->text('consumer_key');       // stored encrypted via model cast
            $table->text('consumer_secret');    // stored encrypted via model cast
            $table->string('api_version')->default('wc/v3');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_stores');
    }
};
