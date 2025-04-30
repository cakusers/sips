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
        Schema::create('waste_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('qty_in_gram');
            $table->integer('transaction_waste_price');
            $table->foreignId('transaction_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_transaction');
    }
};
