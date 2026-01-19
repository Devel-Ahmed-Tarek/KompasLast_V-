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
        Schema::table('offers', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('type_id')->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('country_id')->constrained('cities')->nullOnDelete();
            
            // Indexes for performance
            $table->index('country_id');
            $table->index('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropIndex(['country_id']);
            $table->dropIndex(['city_id']);
            $table->dropColumn(['country_id', 'city_id']);
        });
    }
};
