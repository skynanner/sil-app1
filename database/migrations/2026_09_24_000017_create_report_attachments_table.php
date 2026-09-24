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
        Schema::create('report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_report_id')->constrained('travel_reports')->cascadeOnDelete();
            $table->string('attachment_type', 30); // BOARDING_PASS, TICKET, TRANSPORT_PROOF, HOTEL_INVOICE, OTHER
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_attachments');
    }
};
