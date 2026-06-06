<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('ingredients_title')->nullable();
            $table->json('ingredients')->nullable();
            $table->string('benefits_title')->nullable();
            $table->json('benefits')->nullable();
            $table->string('how_to_use_title')->nullable();
            $table->json('how_to_use')->nullable();
            $table->string('reviews_title')->nullable();
            $table->json('customer_reviews')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('checkout_title')->nullable();
            $table->string('final_cta_title')->nullable();
            $table->text('final_cta_text')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
