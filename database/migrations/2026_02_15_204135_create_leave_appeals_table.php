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
        Schema::create('leave_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->foreignId('appealed_by')->constrained('users');
            $table->foreignId('decided_by')->nullable()->constrained('users');

            // Appeal content
            $table->text('reason')->comment('Reason for appeal/reconsideration');
            $table->text('additional_info')->nullable()->comment('Additional information supporting the appeal');

            // Decision
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->text('decision_note')->nullable()->comment('Admin decision note');
            $table->string('decision')->nullable()->comment('approved, denied');

            // Dates
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('leave_request_id');
            $table->index('appealed_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_appeals');
    }
};
