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
        Schema::create('employee_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('travel_request_id')->nullable()->constrained('travel_requests')->nullOnDelete();
            $table->string('reason', 500);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, TGR_PROCESS, RESOLVED
            $table->string('tgr_reference', 100)->nullable();
            $table->timestamp('blocked_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_blocks');
    }
};
