<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('woo_store_id')->constrained('woo_stores')->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('status'); // pending, running, success, failed, partial
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('error')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['woo_store_id', 'entity_type']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_sync_logs');
    }
};
