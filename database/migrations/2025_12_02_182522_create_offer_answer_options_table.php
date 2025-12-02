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
        Schema::create('offer_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_answer_id')->constrained('offer_answers')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('question_options')->cascadeOnDelete();
            
            $table->timestamps();
            
            // Unique: منع التكرار
            $table->unique(['offer_answer_id', 'option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_answer_options');
    }
};
