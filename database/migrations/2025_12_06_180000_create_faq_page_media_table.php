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
        Schema::create('faq_page_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_page_id');
            $table->string('field_name'); // مثل: hero_image, banner, icon, etc.
            $table->string('language', 2); // en, de, fr, it, ar
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // image, video, document
            $table->integer('file_size')->nullable(); // بالـ KB
            $table->json('metadata')->nullable(); // معلومات إضافية (alt, title, description مترجم)
            $table->integer('order')->default(0); // للترتيب لو في أكثر من صورة لنفس الحقل واللغة
            $table->timestamps();

            $table->foreign('faq_page_id')
                ->references('id')
                ->on('faq_pages')
                ->onDelete('cascade');

            // فهرس فريد: نفس الحقل + نفس اللغة + نفس الترتيب = صورة واحدة
            $table->unique(['faq_page_id', 'field_name', 'language', 'order'], 'unique_faq_page_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_page_media');
    }
};

