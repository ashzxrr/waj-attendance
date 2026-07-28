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
        Schema::create('employee_auth', function (Blueprint $table) {
            $table->id();
            $table->string('pin')->unique();
            $table->foreign('pin')->references('pin')->on('employees_cache')->onDelete('cascade');
            $table->string('pin_absensi');
            $table->string('device_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_auth');
    }
};
