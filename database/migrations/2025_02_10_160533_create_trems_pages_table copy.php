<?php

use App\Models\TremsPage;
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
        Schema::create('trems_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->json('meta_key')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();
        });

        TremsPage::create([
            'title'            => 'jkfh',
            'body'             => 'kjhdf',
            'meta_key'         => 'd',
            'meta_description' => 'd',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_pages');
    }
};
