<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('delegate_atasan_id')->nullable()->constrained('users')->nullOnDelete()->after('atasan_id');
            $table->date('delegate_start')->nullable()->after('delegate_atasan_id');
            $table->date('delegate_end')->nullable()->after('delegate_start');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['delegate_atasan_id']);
            $table->dropColumn(['delegate_atasan_id', 'delegate_start', 'delegate_end']);
        });
    }
};
