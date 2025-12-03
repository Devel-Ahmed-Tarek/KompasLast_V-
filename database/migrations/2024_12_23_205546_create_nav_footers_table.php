<?php

use App\Models\NavFooter;
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
        Schema::create('nav_footers', function (Blueprint $table) {
            $table->id();
            $table->json('home')->nullable();
            $table->json('services')->nullable();
            $table->json('aboutUs')->nullable();
            $table->json('blogs')->nullable();
            $table->json('faqs')->nullable();
            $table->json('compalins')->nullable();
            $table->json('imprint')->nullable();
            $table->json('label_input')->nullable();
            $table->json('contactUs')->nullable();
            $table->json('button')->nullable();
            $table->json('loin_btn')->nullable();
            $table->timestamps();
        });

        NavFooter::create([
            'home'        => [
                'en' => 'ajkshdajs',
                'de' => 'ajkshdajs',
                'fr' => 'ajkshdajs',
                'it' => 'ajkshdajs',
            ],
            'services'    => null,
            'aboutUs'     => null,
            'blogs'       => null,
            'faqs'        => null,
            'compalins'   => null,
            'imprint'     => null,
            'label_input' => null,
            'contactUs'   => null,
            'button'      => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_footers');
    }
};