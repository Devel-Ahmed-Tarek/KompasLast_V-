<?php

use App\Models\Home;
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
        Schema::create('homes', function (Blueprint $table) {
            $table->id();
            $table->string('hero_imge')->nullable();
            $table->json('hero_title')->nullable();
            $table->json('hero_sub_title')->nullable();
            $table->json('hero_button')->nullable();
            $table->json('services_title')->nullable();
            $table->json('work_title')->nullable();
            $table->json('work_name')->nullable();
            $table->json('work_name2')->nullable();
            $table->json('work_name3')->nullable();
            $table->string('work_icon')->nullable();
            $table->string('work_icon2')->nullable();
            $table->string('work_icon3')->nullable();
            $table->json('work_description')->nullable();
            $table->json('work_description2')->nullable();
            $table->json('work_description3')->nullable();
            $table->json('review_form_title')->nullable();
            $table->json('review_form_sub_title')->nullable();
            $table->json('our_clients_pinions')->nullable();
            $table->json('faq_title')->nullable();
            $table->string('faq_image')->nullable();
            $table->json('our_trusted_Companies')->nullable();
            $table->json('sub_our_trusted_Companies')->nullable();
            $table->json('hero_description')->nullable();
            $table->json('what_clients_say_cabout')->nullable();
            $table->json('slug')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_titel')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('our_clients_pinions_sub_title')->nullable();
            $table->json('faq_sub_title')->nullable();
            $table->json('work_sub_title')->nullable();
            $table->json('services_sub_title')->nullable();
            $table->timestamps();
        });

        Home::create([
            'faq_title' => ['en' => 'kjhafkjsdf'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homes');
    }
};
