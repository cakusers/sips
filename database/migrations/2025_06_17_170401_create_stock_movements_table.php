<?php

use App\Enums\MovementType;
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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                MovementType::MANUALIN->value,
                MovementType::MANUALOUT->value,
                MovementType::PURCHASEIN->value,
                MovementType::SELLOUT->value,
                MovementType::RETURNEDIN->value,
                MovementType::RETURNEDOUT->value,
                MovementType::SORTINGIN->value,
                MovementType::SORTINGOUT->value,
                MovementType::SORTINGADJUST->value,
            ]);
            $table->float('before_movement_kg');
            $table->float('quantity_change_kg');
            $table->float('current_stock_after_movement_kg');
            $table->text('description')->nullable();
            $table->float('carbon_footprint_change_kg_co2e')->default(0);
            $table->foreignId('waste_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
