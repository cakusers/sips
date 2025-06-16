<?php

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            ])
                ->default(TransactionStatus::NEW->value);
            $table->enum('payment_status', [
                PaymentStatus::UNPAID->value,
                PaymentStatus::PAID->value
            ])
                ->default(PaymentStatus::UNPAID->value);
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
