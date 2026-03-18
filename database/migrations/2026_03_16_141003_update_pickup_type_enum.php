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
        Schema::table('pickups', function (Blueprint $table) {
            // Change type column to support more waste types
            $table->enum('type', [
                'Plastik',
                'Kertas', 
                'Logam',
                'Kaca',
                'Organik',
                'Elektronik',
                'Lainnya'
            ])->change()->comment('Jenis sampah yang diserahkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('type', ['Plastik', 'Kertas'])->change();
        });
    }
};