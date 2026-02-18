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
        Schema::create('leave_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');

            // Original dates
            $table->date('original_start_date');
            $table->date('original_end_date');

            // Requested new dates
            $table->date('requested_start_date');
            $table->date('requested_end_date');

            // Reason for amendment
            $table->text('reason');

            // Status: pending, approved, rejected
            $table->string('status')->default('pending');

            // Approval information
            $table->text('approval_note')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('leave_request_id');
            $table->index('requested_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_amendments');
    }
};
