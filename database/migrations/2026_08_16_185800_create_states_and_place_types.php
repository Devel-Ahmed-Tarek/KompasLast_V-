<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->json('name');
            $table->string('code', 8)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index('country_id');
            $table->unique(['country_id', 'code']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->nullOnDelete();
            $table->string('place_type', 32)->default('city')->after('state_id');
            $table->index(['country_id', 'place_type']);
            $table->index(['state_id', 'place_type']);
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('state_id');
            $table->dropIndex(['country_id', 'place_type']);
            $table->dropColumn('place_type');
        });

        Schema::dropIfExists('states');
    }
};
