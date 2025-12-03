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
            $table->foreign('parent_option_id')
                  ->references('id')
                  ->on('question_options')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_questions', function (Blueprint $table) {
            $table->dropForeign(['parent_option_id']);
        });
    }
};
