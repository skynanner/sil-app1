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
        Schema::create('travel_monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->date('activity_start_date');
            $table->date('activity_end_date');
            $table->decimal('approved_amount', 18, 2)->nullable();
            $table->string('report_status', 20)->default('PENDING'); // PENDING, SUBMITTED, OVERDUE
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_monitoring');
    }
};
