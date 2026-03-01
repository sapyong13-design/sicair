<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // #48: Notification preferences per user (JSON)
            // Keys: 'email_on_approve', 'email_on_reject', 'email_on_pending', 'email_on_decision'
            // Default: all enabled
            $table->text('notification_preferences')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
