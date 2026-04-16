<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sender_id')->nullable();
            $table->string('api_key')->nullable();
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->timestamp('last_balance_checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
