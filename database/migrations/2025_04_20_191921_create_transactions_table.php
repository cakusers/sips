<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [TransactionType::PURCHASE->value, TransactionType::SELL->value]);
            $table->enum('status', [
                TransactionStatus::NEW->value,
                TransactionStatus::COMPLETE->value,
                TransactionStatus::PROCESSING->value,
                TransactionStatus::RETURNED->value,
                TransactionStatus::DELIVERED->value,
                TransactionStatus::CANCELED->value
            ])->default(TransactionStatus::NEW->value);
            $table->integer('total_price')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
