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
        Schema::create('about_us', function (Blueprint $table) {

            $table->id();

            $table->json('title')->nullable();

            $table->string('hero_image')->nullable();
            $table->json('hero_title')->nullable();
            $table->json('hero_decsription')->nullable();

            $table->json('why_title')->nullable();
            $table->json('why_sub_title')->nullable();

            $table->json('why_name')->nullable();
            $table->json('why_name2')->nullable();
            $table->json('why_name3')->nullable();

            $table->string('why_imge')->nullable();
            $table->string('why_imge2')->nullable();
            $table->string('why_imge3')->nullable();

            $table->json('why_decsription')->nullable();
            $table->json('why_decsription2')->nullable();
            $table->json('why_decsription3')->nullable();

            $table->json('target_title')->nullable();
            $table->string('target_imge')->nullable();
            $table->json('target_body')->nullable();

            $table->string('informaion_customer_cont')->nullable();
            $table->string('informaion_company_cont')->nullable();
            $table->string('informaion_offer_cont')->nullable();

            $table->json('informaion_company_decsription')->nullable();
            $table->json('informaion_offer_decsription')->nullable();
            $table->json('informaion_customer_decsription')->nullable();

            $table->json('informaion_company_name')->nullable();
            $table->json('informaion_offer_name')->nullable();
            $table->json('informaion_customer_name')->nullable();

            $table->json('slug')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
