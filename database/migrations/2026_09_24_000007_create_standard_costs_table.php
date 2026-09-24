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
        Schema::create('standard_costs', function (Blueprint $table) {
            $table->id();
            $table->string('cost_type', 30); // TRANSPORT, HOTEL, AIRFARE, DAILY_ALLOWANCE, OTHER
            $table->string('name', 200);
            $table->string('region_origin', 100)->nullable();
            $table->string('region_destination', 100)->nullable();
            $table->string('rank_group', 50)->nullable();
            $table->string('unit', 30); // per day, per trip, per night
            $table->decimal('unit_amount', 18, 2);
            $table->integer('fiscal_year');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_costs');
    }
};
