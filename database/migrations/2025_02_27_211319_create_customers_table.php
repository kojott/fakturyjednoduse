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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ico')->comment('IČO - identifikační číslo osoby');
            $table->string('company_name');
            $table->string('address');
            $table->string('city');
            $table->string('zip_code');
            $table->string('country')->default('Česká republika');
            $table->string('dic')->nullable()->comment('DIČ - daňové identifikační číslo');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'ico']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
