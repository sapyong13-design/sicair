<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_leave_user_status');
            $table->index(['status', 'created_at'], 'idx_leave_status_date');
            $table->index(['start_date', 'end_date'], 'idx_leave_dates');
            $table->index('atasan_reviewer_id', 'idx_leave_atasan');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_audit_user_date');
            $table->index('action', 'idx_audit_action');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_notif_user_read');
            $table->index('created_at', 'idx_notif_date');
        });

        Schema::table('cuti_records', function (Blueprint $table) {
            $table->index(['user_id', 'year'], 'idx_cuti_user_year');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_user_status');
            $table->dropIndex('idx_leave_status_date');
            $table->dropIndex('idx_leave_dates');
            $table->dropIndex('idx_leave_atasan');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_user_date');
            $table->dropIndex('idx_audit_action');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_read');
            $table->dropIndex('idx_notif_date');
        });

        Schema::table('cuti_records', function (Blueprint $table) {
            $table->dropIndex('idx_cuti_user_year');
        });
    }
};
