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
        Schema::create('balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year');
            $table->string('jenis_cuti')->default('Cuti Tahunan');

            // Adjustment details
            $table->integer('adjustment_days')->comment('Positive for addition, negative for deduction');
            $table->text('reason')->comment('Reason for adjustment');
            $table->string('type')->comment('addition, deduction, correction');

            // Approval workflow
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('approval_note')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Audit
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'year']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_adjustments');
    }
};
