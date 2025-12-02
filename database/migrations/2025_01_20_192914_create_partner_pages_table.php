<?php

use App\Models\PartnerPage;
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
        Schema::create('partner_pages', function (Blueprint $table) {
            $table->id();
            $table->json('title')->nullable();
            $table->json('sub_title')->nullable();
            $table->json('first_section_title')->nullable();
            $table->json('first_section_sub_title')->nullable();
            $table->json('join_sud_title')->nullable();
            $table->json('body')->nullable();
            $table->string('image_card1')->nullable();
            $table->string('image_card2')->nullable();
            $table->string('image_card3')->nullable();
            $table->json('title_card1')->nullable();
            $table->json('title_card2')->nullable();
            $table->json('title_card3')->nullable();
            $table->json('description_card1')->nullable();
            $table->json('description_card2')->nullable();
            $table->json('description_card3')->nullable();
            $table->json('join_title')->nullable();
            $table->string('join_step_image1')->nullable();
            $table->string('join_step_image2')->nullable();
            $table->string('join_step_image3')->nullable();
            $table->json('join_step_title1')->nullable();
            $table->json('join_step_title2')->nullable();
            $table->json('join_step_title3')->nullable();
            $table->json('join_step_description1')->nullable();
            $table->json('join_step_description2')->nullable();
            $table->json('join_step_description3')->nullable();
            $table->json('last_section_title')->nullable();
            $table->json('last_section_description')->nullable();
            $table->json('last_section_btn')->nullable();
            $table->json('last_section_login_title')->nullable();
            $table->json('last_section_login_sub_title')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();
        });

        PartnerPage::create([
            'title'            => [
                'en' => 'kdjsfdfs',
                'de' => 'kdjsfdfs',
                'fr' => 'kdjsfdfs',
                'it' => 'kdjsfdfs'],
            'meta_key'         => [
                'en' => 'jkghgdfd',
                'it' => 'jkghgdfd',
                'fr' => 'jkghgdfd',
                'de' => 'jkghgdfd',
            ],
            'meta_description' => [
                'en' => 'jkghgdfd',
                'it' => 'jkghgdfd',
                'fr' => 'jkghgdfd',
                'de' => 'jkghgdfd',
            ],
            'body'             => [
                'en' => 'kdjsfdfs',
                'de' => 'kdjsfdfs',
                'fr' => 'kdjsfdfs',
                'it' => 'kdjsfdfs',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_pages');
    }
};
