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
        Schema::create('travel_request_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('activity_start_date');
            $table->date('activity_end_date');
            $table->unique(['travel_request_id', 'employee_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_request_personnel');
    }
};
