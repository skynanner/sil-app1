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
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_unit_id')->constrained('work_units')->cascadeOnDelete();
            $table->integer('fiscal_year');
            $table->string('account_code', 10);
            $table->string('description', 255)->nullable();
            $table->decimal('total_amount', 18, 2);
            $table->decimal('committed_amount', 18, 2)->default(0);
            $table->decimal('realized_amount', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};
