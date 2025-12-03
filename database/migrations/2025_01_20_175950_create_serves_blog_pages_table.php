<?php

use App\Models\ServesBlogPage;
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
        Schema::create('serves_blog_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        ServesBlogPage::create([
            'title'       => [
                'en' => 'Latest News & Articles',
                'de' => 'Последние новости и статьи',
                'fr' => 'Останні новини та статті',
                'it' => 'Останні новини та статті',
            ],
            'sub_title'   => [
                'en' => 'Latest News & Articles',
                'de' => 'Последние новости и статьи',
                'fr' => 'Останні новини та статті',
                'it' => 'Останні новини та статті',
            ],
            'description' => [
                'en' => 'Latest News & Articles',
                'de' => 'Последние новости и статьи',
                'fr' => 'Останні новини та статті',
                'it' => 'Останні новини та статті',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serves_blog_pages');
    }
};
