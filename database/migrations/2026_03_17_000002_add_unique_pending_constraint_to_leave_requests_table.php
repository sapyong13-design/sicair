<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a unique index on (user_id, start_date) to prevent duplicate pending
     * leave requests for the same user on the same start date.
     *
     * Note: This is a table-level unique constraint. Application logic should
     * further filter by status='diajukan'/'pending' before relying on this.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unique(['user_id', 'start_date'], 'uq_leave_user_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropUnique('uq_leave_user_start_date');
        });
    }
};
