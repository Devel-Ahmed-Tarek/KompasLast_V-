<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::create('reviews_company', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->unsignedTinyInteger('stars')->comment('Number of stars (1 to 5)');
            $table->text('comment')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['company'])->default('company')->comment('Review type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews_company');
    }
};
