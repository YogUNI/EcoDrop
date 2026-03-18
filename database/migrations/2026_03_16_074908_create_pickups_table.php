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
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
    
    $table->string('type');
    $table->decimal('weight', 8, 2);
    $table->string('status')->default('pending');
    $table->integer('points_earned')->default(0);
    $table->date('pickup_date');
    $table->string('address');        // ← TAMBAH
    $table->string('phone', 20);      // ← TAMBAH
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};