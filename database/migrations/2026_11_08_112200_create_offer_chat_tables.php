<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('customer_last_read_at')->nullable();
            $table->timestamp('company_last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['offer_id', 'company_id']);
        });

        Schema::create('offer_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('offer_conversations')->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'company']);
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_messages');
        Schema::dropIfExists('offer_conversations');
    }
};
