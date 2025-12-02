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
        Schema::create('visitor_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id');   // ربط بالزائر
            $table->string('url');                      // الرابط الذي زاره
            $table->integer('visit_count')->default(1); // عدد مرات الزيارة
            $table->timestamps();

            $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_links');
    }
};
