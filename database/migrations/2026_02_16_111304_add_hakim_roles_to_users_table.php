<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add new roles: hakim, hakim_ad_hoc
     * These roles have same permissions as pegawai but with hierarchical approval:
     * hakim/hakim_ad_hoc → kepegawaian (atasan) → Ketua Pengadilan
     */
    public function up(): void
    {
        // Migration is informational - roles are stored as strings in the 'role' column
        // New roles available: 'hakim', 'hakim_ad_hoc'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Roles are handled via application logic, not schema
    }
};
