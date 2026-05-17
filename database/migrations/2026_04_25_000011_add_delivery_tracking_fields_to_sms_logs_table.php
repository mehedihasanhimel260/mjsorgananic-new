<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->foreignId('sms_campaign_id')->nullable()->after('user_id')->constrained('sms_campaigns')->nullOnDelete();
            $table->string('campaign_key', 40)->nullable()->after('send_type');
            $table->string('gateway_transaction_id', 100)->nullable()->after('campaign_key');
            $table->string('delivery_status', 30)->nullable()->after('status_text');
            $table->unsignedInteger('delivery_attempts')->default(0)->after('delivery_status');
            $table->timestamp('delivery_status_checked_at')->nullable()->after('delivery_attempts');
            $table->timestamp('delivery_finalized_at')->nullable()->after('delivery_status_checked_at');

            $table->index(['phone', 'send_type', 'created_at'], 'sms_logs_phone_type_created_idx');
            $table->index(['delivery_status', 'delivery_finalized_at'], 'sms_logs_delivery_status_idx');
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropForeign(['sms_campaign_id']);
            $table->dropIndex('sms_logs_phone_type_created_idx');
            $table->dropIndex('sms_logs_delivery_status_idx');
            $table->dropIndex(['gateway_transaction_id']);
            $table->dropColumn([
                'sms_campaign_id',
                'campaign_key',
                'gateway_transaction_id',
                'delivery_status',
                'delivery_attempts',
                'delivery_status_checked_at',
                'delivery_finalized_at',
            ]);
        });
    }
};
