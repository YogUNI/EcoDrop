<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // 'approved', 'rejected', 'deleted'
            $table->foreignId('pickup_id')->nullable()->constrained('pickups')->onDelete('set null');
            $table->string('user_name'); // nama user yang punya setoran
            $table->string('waste_type')->nullable(); // jenis sampah
            $table->decimal('waste_weight', 8, 2)->nullable(); // berat sampah
            $table->integer('points_given')->nullable(); // poin yang diberikan (kalau approved)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};