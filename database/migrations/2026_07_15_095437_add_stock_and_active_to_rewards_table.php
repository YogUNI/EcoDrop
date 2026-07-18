<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // Stock: null = unlimited, integer = terbatas
            $table->unsignedInteger('stock')->nullable()->after('description');
            // is_active: false = reward disembunyikan dari katalog user
            $table->boolean('is_active')->default(true)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn(['stock', 'is_active']);
        });
    }
};
