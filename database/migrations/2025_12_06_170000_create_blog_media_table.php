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
        Schema::create('blog_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_id');
            $table->string('field_name'); // مثل: main_image, image, gallery, etc.
            $table->string('language', 2); // en, de, fr, it, ar
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // image, video, document
            $table->integer('file_size')->nullable(); // بالـ KB
            $table->json('metadata')->nullable(); // معلومات إضافية (alt, title, description مترجم)
            $table->integer('order')->default(0); // للترتيب لو في أكثر من صورة لنفس الحقل واللغة
            $table->timestamps();

            $table->foreign('blog_id')
                ->references('id')
                ->on('blogs')
                ->onDelete('cascade');

            // فهرس فريد: نفس الحقل + نفس اللغة + نفس الترتيب = صورة واحدة
            $table->unique(['blog_id', 'field_name', 'language', 'order'], 'unique_blog_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_media');
    }
};

