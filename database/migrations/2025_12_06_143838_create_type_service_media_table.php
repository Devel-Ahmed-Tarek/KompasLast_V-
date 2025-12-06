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
        Schema::create('type_service_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_ditali_service_id');
            $table->string('field_name'); // مثل: small_image, main_image, feature_image, etc.
            $table->string('language', 2); // en, de, fr, it, ar
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // image, video, document
            $table->integer('file_size')->nullable(); // بالـ KB
            $table->json('metadata')->nullable(); // معلومات إضافية (alt, title, description مترجم)
            $table->integer('order')->default(0); // للترتيب لو في أكثر من صورة لنفس الحقل واللغة
            $table->timestamps();

            $table->foreign('type_ditali_service_id')
                ->references('id')
                ->on('type_ditali_services')
                ->onDelete('cascade');

            // فهرس فريد: نفس الحقل + نفس اللغة + نفس الترتيب = صورة واحدة
            $table->unique(['type_ditali_service_id', 'field_name', 'language', 'order'], 'unique_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_service_media');
    }
};
