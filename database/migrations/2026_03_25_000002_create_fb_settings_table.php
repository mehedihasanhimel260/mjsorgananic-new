<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fb_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fb_page_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('pixel_id')->nullable();
            $table->string('event_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_settings');
    }
};
