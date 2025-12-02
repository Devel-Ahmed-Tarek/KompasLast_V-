<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('type_ditali_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id');
            // $table->json('title')->nullable(); // Multilingual (if needed)
            $table->json('small_image')->nullable();
            $table->json('main_image')->nullable();
            $table->string('service_home_icon')->nullable();
            $table->json('short_description')->nullable(); // Multilingual
            $table->json('feature_header')->nullable();    // Multilingual
            $table->json('feature_sub_title')->nullable(); // Multilingual
            $table->json('body')->nullable();              // Multilingual
            $table->json('tips_title')->nullable();        // Multilingual
            $table->json('tips_subtitle')->nullable();     // Multilingual
            $table->json('slug')->nullable();
            $table->json('meta_keys')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable(); // Multilingual
            $table->json('blog_meta_keys')->nullable();
            $table->json('blog_meta_title')->nullable();
            $table->json('blog_meta_description')->nullable(); // Multilingual
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_ditali_services');
    }
};
