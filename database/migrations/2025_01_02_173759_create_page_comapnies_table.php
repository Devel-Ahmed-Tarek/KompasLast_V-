<?php

use App\Models\PageCompany;
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
        Schema::create('page_comapnies', function (Blueprint $table) {
            $table->id();
            $table->json('title')->nullable();
            $table->json('sub_title')->nullable();
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->json('form_title')->nullable();
            $table->json('image_title')->nullable();
            $table->json('slug')->unique()->nullable();
            $table->json('meta_key');
            $table->json('meta_title');
            $table->json('meta_description');
            $table->timestamps();
        });

        PageCompany::create([
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
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_comapnies');
    }
};
