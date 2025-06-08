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
        Schema::create('waste_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('purchase_per_kg');
            $table->integer('selling_per_kg');
            $table->foreignId('waste_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('effective_start_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_prices');
    }
};
