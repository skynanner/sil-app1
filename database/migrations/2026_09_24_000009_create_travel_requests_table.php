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
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->string('sprint_number', 100)->unique();
            $table->date('sprint_date');
            $table->string('sprint_file_path', 500);
            $table->string('activity_name', 255)->nullable();
            $table->string('activity_location', 255);
            $table->date('activity_start_date');
            $table->date('activity_end_date');
            $table->foreignId('budget_allocation_id')->nullable()->constrained('budget_allocations')->nullOnDelete();
            $table->foreignId('submitted_by')->constrained('users');
            $table->string('status', 30)->default('WAITING_VERIFICATION'); // WAITING_VERIFICATION, IN_PROCESS, PROBLEM, SP2D
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
