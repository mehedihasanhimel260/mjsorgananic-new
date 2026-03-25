<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fb_settings', function (Blueprint $table) {
            $table->string('verify_token')->nullable()->after('event_id');
        });
    }

    public function down(): void
    {
        Schema::table('fb_settings', function (Blueprint $table) {
            $table->dropColumn('verify_token');
        });
    }
};
