<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blogs_pages', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->json('title')->nullable();
            $table->json('sub_title')->nullable();
            $table->json('description')->nullable();
            $table->json('slug')->nullable();
            $table->json('blog_categories')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();
        });

        DB::table('blogs_pages')->insert([
            'title' => json_encode([
                'en' => 'Blog Category',
                'de' => 'Blog Category',
                'fr' => 'Blog Category',
                'it' => 'Blog Category',
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs_pages');
    }
};
