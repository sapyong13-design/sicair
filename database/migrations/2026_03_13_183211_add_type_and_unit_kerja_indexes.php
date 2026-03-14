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
        // Index for laporan tahunan GROUP BY user_id, type
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['user_id', 'type'], 'idx_leave_user_type');
            $table->index('type', 'idx_leave_type');
        });

        // Index for unit_kerja filter in whereHas queries
        Schema::table('users', function (Blueprint $table) {
            $table->index('unit_kerja', 'idx_users_unit_kerja');
            $table->index('atasan_id', 'idx_users_atasan');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_user_type');
            $table->dropIndex('idx_leave_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_unit_kerja');
            $table->dropIndex('idx_users_atasan');
        });
    }
};
