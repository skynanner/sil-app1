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
        Schema::create('travel_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_file_path', 500)->nullable();
            $table->date('due_date');
            $table->string('status', 20)->default('PENDING'); // PENDING, SUBMITTED, OVERDUE
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_reports');
    }
};
