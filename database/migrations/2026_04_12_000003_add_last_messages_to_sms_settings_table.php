<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->text('last_bulk_message')->nullable()->after('last_balance_checked_at');
            $table->text('last_single_message')->nullable()->after('last_bulk_message');
        });
    }

    public function down(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn(['last_bulk_message', 'last_single_message']);
        });
    }
};
