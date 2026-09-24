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
        Schema::create('travel_cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculation_id')->constrained('travel_cost_calculations')->cascadeOnDelete();
            $table->foreignId('personnel_id')->constrained('travel_request_personnel')->cascadeOnDelete();
            $table->foreignId('standard_cost_id')->nullable()->constrained('standard_costs')->nullOnDelete();
            $table->string('description', 255)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_amount', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_cost_items');
    }
};
