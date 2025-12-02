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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->json('main_image');
            $table->json('image');
            $table->json('short_description');
            $table->json('body');
            $table->json('key_key');
            $table->json('meta_title');
            $table->json('key_description');
            $table->json('btn');
            $table->string('slug')->unique()->nullable();
            $table->boolean('status')->default(1);
            $table->string('btn_hrf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
