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
        Schema::table('reviews_company', function (Blueprint $table) {
            $table->boolean('status')
                ->default(false) // القيمة الافتراضية false (غير موافق عليه)
                ->after('type')
                ->comment('Status of the review: 0 => Pending, 1 => Approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews_company', function (Blueprint $table) {
            //
        });
    }
};
