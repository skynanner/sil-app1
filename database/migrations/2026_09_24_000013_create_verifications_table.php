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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->foreignId('verifier_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage', 10); // PPK, PPSPM
            $table->string('decision', 10); // ACCEPTED, REJECTED
            $table->text('notes')->nullable();
            $table->timestamp('verified_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
