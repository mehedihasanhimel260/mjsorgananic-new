<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->boolean('schedule_enabled')->default(true)->after('transaction_type');
            $table->unsignedTinyInteger('schedule_day_of_week')->default(5)->after('schedule_enabled');
            $table->string('schedule_time', 5)->default('10:00')->after('schedule_day_of_week');
            $table->date('schedule_start_date')->nullable()->after('schedule_time');
        });
    }

    public function down(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_enabled',
                'schedule_day_of_week',
                'schedule_time',
                'schedule_start_date',
            ]);
        });
    }
};
