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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->enum('type', ['regular', 'proforma', 'advance'])->default('regular');
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('total_without_vat', 10, 2)->default(0);
            $table->decimal('total_with_vat', 10, 2)->default(0);
            $table->string('currency', 3)->default('CZK');
            $table->enum('status', ['new', 'paid', 'cancelled'])->default('new');
            $table->text('note')->nullable();
            $table->string('payment_method')->default('bank_transfer');
            $table->string('bank_account')->nullable();
            $table->string('qr_payment_code')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
