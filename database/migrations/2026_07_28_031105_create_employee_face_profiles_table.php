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
        Schema::create('employee_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('pin');
            $table->longText('face_embedding');
            $table->string('photo_reference_path')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->foreign('pin')->references('pin')->on('employees_cache')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_face_profiles');
    }
};
