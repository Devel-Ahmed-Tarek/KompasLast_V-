<?php

use App\Models\ImprintPage;
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
        Schema::create('imprint_pages', function (Blueprint $table) {
            $table->id();
            $table->json('title')->nullable();
            $table->json('sub_title')->nullable();
            $table->json('body')->nullable();
            $table->json('meta_key')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();
        });

        // create additional tables for imprint_contacts, imprint_address, imprint_social_media, etc.
        ImprintPage::create([
            'title'            => [
                'en' => 'Impressum',
                'de' => 'Impressum',
                'fr' => 'Impressum',
                'it' => 'Impresso',
            ],
            'body'             => [
                'en' => 'Impressum',
                'de' => 'Impressum',
                'fr' => 'Impressum',
                'it' => 'Impresso',
            ],
            'meta_key'         => [
                'en' => 'Impressum Keywords',
                'de' => 'Impressum Schlüsselwörter',
                'fr' => 'Mots-clés d\'impression',
                'it' => 'Parole chiave per l\'impresso',
            ],
            'meta_description' => [
                'en' => 'Impressum Description',
                'de' => 'Impressum Beschreibung',
                'fr' => 'Description d\'impression',
                'it' => 'Descrizione per l\'impresso',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imprint_pages');
    }
};
