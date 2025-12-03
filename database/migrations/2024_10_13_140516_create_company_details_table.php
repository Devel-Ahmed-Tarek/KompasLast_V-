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
        Schema::create('company_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->integer('count_offer')->default(0);
            $table->float('total')->default(0);
            $table->boolean('sucsses')->default(0);
            $table->boolean('status')->default(0);
            $table->string('loge1')->nullable();
            $table->string('file')->nullable();
            $table->string('file2')->nullable();
            $table->string('file3')->nullable();
            $table->string('description')->nullable();
            $table->string('reg_name');
            $table->string('founded_year');
            $table->string('owner_name');
            $table->string('website');
            $table->string('address');
            $table->string('country');
            $table->string('phone2')->nullable();
            $table->string('number');
            $table->string('counties');
            $table->string('about');
            $table->string('banc_name');
            $table->string('ZIPCode');
            $table->string('city');
            $table->string('banc_count');
            $table->string('banc_ip');
            $table->json('files_links');
            $table->dateTime('exp_date');
            $table->integer('receive_offers')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_details');
    }
};
