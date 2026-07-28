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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pin');
            $table->dateTime('datetime');
            $table->date('tanggal');
            $table->enum('type', ['IN', 'OUT']);
            $table->string('photo_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_from_office', 8, 2)->nullable();
            $table->boolean('is_within_geofence')->default(false);
            $table->decimal('face_match_score', 5, 2)->nullable();
            $table->boolean('face_verified')->default(false);
            $table->string('device_info')->nullable();
            $table->enum('status', ['pending', 'verified', 'flagged'])->default('pending');
            $table->timestamp('synced_to_hris_at')->nullable();
            $table->timestamps();

            $table->index(['pin', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
