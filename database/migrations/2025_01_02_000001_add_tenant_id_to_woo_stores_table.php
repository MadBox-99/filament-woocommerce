<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woo_stores', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('is_active');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('woo_stores', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
