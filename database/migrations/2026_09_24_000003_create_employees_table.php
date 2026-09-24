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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 18)->unique();
            $table->string('nik', 16)->unique();
            $table->string('full_name', 200);
            $table->string('rank_grade', 50);
            $table->string('position', 200)->nullable();
            $table->foreignId('work_unit_id')->constrained('work_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
