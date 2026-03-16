<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            // Ini Relasinya ke tabel users (Syarat Dosen)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            $table->string('type'); // Jenis sampah (Plastik, Kertas, dll)
            $table->decimal('weight', 8, 2); // Berat sampah (kg)
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->integer('points_earned')->default(0); // Poin yang didapat
            $table->date('pickup_date'); // Tanggal penjemputan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};