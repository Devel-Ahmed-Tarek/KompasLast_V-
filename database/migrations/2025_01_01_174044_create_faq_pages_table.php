<?php

use App\Models\FaqPage;
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
        Schema::create('faq_pages', function (Blueprint $table) {
            $table->id();
            $table->json('hero_image')->nullable();
            $table->json('title')->nullable();
            $table->json('sub_title')->nullable();
            $table->json('form_title')->nullable();
            $table->json('form_sub_title')->nullable();
            $table->json('slug')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description');
            $table->timestamps();
        });
        FaqPage::create([
            'title'            => [
                'en' => 'kdjsfdfs',
                'de' => 'kdjsfdfs',
                'fr' => 'kdjsfdfs',
                'it' => 'kdjsfdfs',
            ],
            'meta_key'         => [
                'en' => 'kdjsfdfs',
                'de' => 'kdjsfdfs',
                'fr' => 'kdjsfdfs',
                'it' => 'kdjsfdfs',
            ],
            'meta_description' => [
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
        Schema::dropIfExists('faq_pages');
    }
};
