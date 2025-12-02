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
        Schema::table('homes', function (Blueprint $table) {
            $table->json('parteners_title');
            $table->json('parteners_sub_title');
            $table->json('parteners_btn');
            $table->json('parteners_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homes', function (Blueprint $table) {
            $table->dropColumn('parteners_title');
            $table->dropColumn('parteners_sub_title');
            $table->dropColumn('parteners_btn');
            $table->dropColumn('parteners_description');
        });
    }
};
