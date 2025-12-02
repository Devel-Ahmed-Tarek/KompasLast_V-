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
        Schema::create('type_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('types')->cascadeOnDelete();

            // JSON field للترجمة (5 لغات: en, de, fr, it, ar)
            $table->json('question_text');

            $table->enum('question_type', [
                'text',
                'single_choice',
                'multi_choice',
                'number',
                'date',
                'email',
                'phone'
            ]);

            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0);

            // للـ Branching (التفرع)
            $table->foreignId('parent_question_id')->nullable()
                ->constrained('type_questions')->nullOnDelete();
            // parent_option_id سيتم إضافته في migration منفصلة بعد إنشاء question_options
            $table->unsignedBigInteger('parent_option_id')->nullable();

            $table->timestamps();

            // Indexes للأداء
            $table->index(['type_id', 'order']);
            $table->index('parent_question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_questions');
    }
};
