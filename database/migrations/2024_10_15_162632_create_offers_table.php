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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->date('date')->nullable();

            $table->string('adresse');
            $table->string('ort');
            $table->string('zimmer');
            $table->string('zipcode');
            $table->string('country');
            $table->string('etage');
            $table->string('vorhanden');

            $table->string('Nach_Adresse')->nullable();
            $table->string('Nach_Ort')->nullable();
            $table->string('Nach_Zimmer')->nullable();
            $table->string('Nach_Etage')->nullable();
            $table->string('Nach_zipcode')->nullable();
            $table->string('Nach_country')->nullable();
            $table->string('Nach_vorhanden')->nullable();

            $table->integer('count');
            $table->string('Besonderheiten')->nullable();

            $table->integer('Number_of_offers')->default(0);
            $table->boolean('cheek')->default(1);
            $table->boolean('status')->default(1);

            $table->string('ip');

            $table->string('country');

            $table->string('city');

            $table->string('lang');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
