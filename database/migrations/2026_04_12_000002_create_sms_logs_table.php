<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('phone', 20);
            $table->text('message');
            $table->string('send_type', 20);
            $table->string('status_code', 20)->nullable();
            $table->string('status_text')->nullable();
            $table->longText('gateway_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
