<?php

use App\Models\Form;
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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            // Multi-language fields stored as JSON
            $table->json('header');
            $table->json('sub_title')->nullable();
            $table->json('step1_title')->nullable();
            $table->json('step2_title')->nullable();
            $table->json('service')->nullable();
            $table->json('name_last');
            $table->json('name_first');
            $table->json('email')->nullable();
            $table->json('phone_number')->nullable();

            $table->json('current_location')->nullable();
            $table->json('current_city')->nullable();
            $table->json('current_rooms_number')->nullable();
            $table->json('current_floor')->nullable();
            $table->json('current_elevator')->nullable();

            $table->json('new_location')->nullable();
            $table->json('new_city')->nullable();
            $table->json('new_rooms_number')->nullable();
            $table->json('new_floor')->nullable();
            $table->json('new_elevator')->nullable();

            $table->json('date')->nullable();
            $table->json('offers_number')->nullable();
            $table->json('other_details')->nullable();
            $table->json('note')->nullable();
            $table->json('next_button')->nullable();
            $table->json('submit_button')->nullable();
            $table->json('success_message')->nullable();
            $table->json('success_message_title')->nullable();

            $table->string('image')->nullable();

            // Timestamps
            $table->timestamps();
        });

        Form::create([
            'header'     => [
                'en' => 'hhjjklsf',
                'de' => 'hhjjklsf',
                'fr' => 'hhjjklsf',
                'it' => 'hhjjklsf',
            ],
            'name_first' => [
                'en' => 'hhjjklsf',
                'de' => 'hhjjklsf',
                'fr' => 'hhjjklsf',
                'it' => 'hhjjklsf',
            ],
            'name_last'  => [
                'en' => 'hhjjklsf',
                'de' => 'hhjjklsf',
                'fr' => 'hhjjklsf',
                'it' => 'hhjjklsf',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
