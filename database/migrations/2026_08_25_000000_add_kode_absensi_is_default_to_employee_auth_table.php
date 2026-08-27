<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_auth', function (Blueprint $table) {
            $table->boolean('kode_absensi_is_default')->default(true)->after('pin_absensi');
        });

        // Existing credentials were generated before this flag existed.
        DB::table('employee_auth')->update(['kode_absensi_is_default' => false]);
    }

    public function down(): void
    {
        Schema::table('employee_auth', function (Blueprint $table) {
            $table->dropColumn('kode_absensi_is_default');
        });
    }
};