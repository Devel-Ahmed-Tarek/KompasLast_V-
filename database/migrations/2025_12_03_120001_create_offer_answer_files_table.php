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
        Schema::create('offer_answer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_answer_id')->constrained('offer_answers')->cascadeOnDelete();
            $table->string('file_path'); // مسار الملف
            $table->string('file_name'); // اسم الملف الأصلي
            $table->string('file_type'); // نوع الملف: image, video, document
            $table->string('mime_type')->nullable(); // MIME type
            $table->integer('file_size')->nullable(); // حجم الملف بالـ bytes
            $table->timestamps();

            // Indexes
            $table->index('offer_answer_id');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_answer_files');
    }
};
