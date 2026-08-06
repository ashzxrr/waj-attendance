<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'rejected' to the status enum
        DB::statement("ALTER TABLE attendance_logs MODIFY COLUMN status ENUM('pending', 'verified', 'flagged', 'rejected') DEFAULT 'pending'");

        // Add review columns
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove review columns
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'review_note']);
        });

        // Revert status enum to original values
        DB::statement("ALTER TABLE attendance_logs MODIFY COLUMN status ENUM('pending', 'verified', 'flagged') DEFAULT 'pending'");
    }
};
