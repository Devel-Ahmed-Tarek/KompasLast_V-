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
            $table->string('confirm_status')->default('pending')->after('status');
            $table->string('confirm_token', 100)->nullable()->after('confirm_status');
            $table->timestamp('confirmed_at')->nullable()->after('confirm_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['confirm_status', 'confirm_token', 'confirmed_at']);
        });
    }
};

