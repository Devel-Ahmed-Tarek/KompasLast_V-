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
        Schema::create('serves_page_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id');
            $table->json('title');
            $table->string('image');
            $table->longText('body');
            $table->json('meta_key')->nullable();
            $table->json('slug')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serves_page_forms');
    }
};
