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
        Schema::table('company_cities', function (Blueprint $table) {
            // 0 = exact city only (legacy behavior). >0 = coverage radius in km.
            $table->unsignedInteger('radius_km')->default(0)->after('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_cities', function (Blueprint $table) {
            $table->dropColumn('radius_km');
        });
    }
};
