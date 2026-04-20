<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_campaign_recipients')) {
            Schema::table('sms_campaign_recipients', function (Blueprint $table) {
                $table->index(['sms_campaign_id', 'batch_number', 'status'], 'sms_campaign_batch_status_idx');
            });

            return;
        }

        Schema::create('sms_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_campaign_id')->constrained('sms_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 20);
            $table->string('week_key', 20);
            $table->unsignedInteger('batch_number')->default(0);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('gateway_transaction_id')->nullable();
            $table->string('status_code', 20)->nullable();
            $table->string('status_text')->nullable();
            $table->longText('gateway_response')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['sms_campaign_id', 'user_id']);
            $table->index(['sms_campaign_id', 'batch_number', 'status'], 'sms_campaign_batch_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_campaign_recipients');
    }
};
