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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address'); // عنوان IP
            $table->string('country')->nullable(); // الدولة
            $table->string('city')->nullable(); // المدينة
            $table->string('device')->nullable(); // نوع الجهاز
            $table->string('browser')->nullable(); // نوع المتصفح
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
