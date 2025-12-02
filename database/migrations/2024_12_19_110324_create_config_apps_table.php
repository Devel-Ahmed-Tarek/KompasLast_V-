<?php

use App\Models\ConfigApp;
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

        Schema::create('config_apps', function (Blueprint $table) {
            $table->id();
            $table->boolean('add_offer')->default(1);
            $table->boolean('offer_flow')->default(1);
            $table->boolean('add_company')->default(1);
            $table->boolean('on_contact')->default(1);
            $table->boolean('on_shop')->default(1);
            $table->boolean('on_auth_company')->default(1);
            $table->boolean('accept_dynamic_offer')->default(1);
            $table->boolean('add_finance_order')->default(1);
            $table->string('file')->nullable();
            $table->string('file2')->nullable();
            $table->string('file3')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('email2')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_number')->nullable();
            $table->string('bank_ip')->nullable();
            $table->string('facebook')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('istagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('twiter')->nullable();
            $table->string('threads')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('qrcode')->nullable();
            $table->timestamps();
        });

        // إنشاء سجل افتراضي في الجدول
        ConfigApp::create([

            'name'    => 'Default App Name',
            'address' => 'Default Address',
            'email'   => 'default@example.com',
            'phone'   => '123456789',
            'website' => 'https://example.com',

        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_apps');
    }
};
