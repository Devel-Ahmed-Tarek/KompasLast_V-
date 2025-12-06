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
        Schema::table('type_questions', function (Blueprint $table) {
            $table->boolean('allows_file_upload')->default(false)->after('is_required');
            $table->string('allowed_file_types')->nullable()->after('allows_file_upload'); // مثلاً: "image,video,document"
            $table->integer('max_files')->nullable()->after('allowed_file_types'); // عدد الملفات المسموح بها
            $table->integer('max_file_size')->nullable()->after('max_files'); // الحجم الأقصى بالـ MB
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_questions', function (Blueprint $table) {
            $table->dropColumn(['allows_file_upload', 'allowed_file_types', 'max_files', 'max_file_size']);
        });
    }
};
