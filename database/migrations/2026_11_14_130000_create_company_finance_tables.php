<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_estimated_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('mode', 16); // single | range
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('amount_from', 12, 2)->nullable();
            $table->decimal('amount_to', 12, 2)->nullable();
            $table->string('currency', 8)->default('EUR');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'offer_id', 'created_at']);
        });

        Schema::create('company_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('type', 32); // credit_topup | offer_purchase | subscription | other
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('EUR');
            $table->string('status', 24)->default('paid'); // paid | pending | cancelled | refunded
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->string('source_type', 32)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->unique(['source_type', 'source_id']);
        });

        Schema::create('company_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('type', 32); // credit_note | refund | wallet_adjustment
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('EUR');
            $table->string('status', 24)->default('completed'); // pending | completed | rejected | cancelled
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_credit_notes');
        Schema::dropIfExists('company_invoices');
        Schema::dropIfExists('offer_estimated_quotes');
    }
};
