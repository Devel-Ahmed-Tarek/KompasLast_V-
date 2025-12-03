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
        Schema::create('offer_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('type_questions')->cascadeOnDelete();
            
            // إجابة المستخدم (نص عادي - غير قابل للترجمة)
            $table->text('answer_text')->nullable();
            
            $table->timestamps();
            
            // Unique: كل سؤال له إجابة واحدة فقط لكل Offer
            $table->unique(['offer_id', 'question_id']);
            
            // Indexes للأداء
            $table->index('offer_id');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_answers');
    }
};
