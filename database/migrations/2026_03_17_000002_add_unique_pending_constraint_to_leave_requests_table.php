<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duplicate prevention for pending leave requests is handled at the
     * application level in LeaveRequestController@store validation.
     * A table-level unique constraint on (user_id, start_date) would wrongly
     * block re-submissions after rejection or cancellation.
     * This migration intentionally does nothing.
     */
    public function up(): void
    {
        // No-op: duplicate prevention handled in application validation layer
    }

    public function down(): void
    {
        // No-op
    }
};
