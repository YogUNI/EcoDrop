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
        Schema::table('users', function (Blueprint $table) {
            // Nambahin kolom role (defaultnya 'user') dan kolom points (defaultnya 0)
            $table->string('role')->default('user')->after('password');
            $table->integer('points')->default(0)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Buat ngehapus kolom kalau kita ngebatalin migration
            $table->dropColumn(['role', 'points']);
        });
    }
};
