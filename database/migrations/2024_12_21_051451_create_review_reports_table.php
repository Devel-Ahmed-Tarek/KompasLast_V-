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
        Schema::create('reviews_company_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviews_company_id')->constrained('reviews_company')->cascadeOnDelete();
            $table->string('comment')->nullable(); // تعليق البلاغ
            $table->string('file')->nullable();
            $table->enum('status', ['open', 'testing', 'pending', 'confirmed', 'canceled'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews_company_reports');
    }
};
