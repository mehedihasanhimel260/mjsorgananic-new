<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->string('username')->nullable()->after('id');
            $table->string('transaction_type', 1)->default('T')->after('api_key');
        });
    }

    public function down(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn(['username', 'transaction_type']);
        });
    }
};
