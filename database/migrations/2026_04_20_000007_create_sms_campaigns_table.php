<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_template_id')->nullable()->constrained('sms_templates')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('campaign_type', 30)->default('weekly');
            $table->string('week_key', 20);
            $table->timestamp('week_starts_at');
            $table->timestamp('week_ends_at');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('batch_size')->default(100);
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('pending_recipients')->default(0);
            $table->unsignedInteger('processing_recipients')->default(0);
            $table->unsignedInteger('sent_recipients')->default(0);
            $table->unsignedInteger('failed_recipients')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_type', 'week_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_campaigns');
    }
};
