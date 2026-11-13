<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->restrictOnDelete();
            $table->foreignId('bed_assignment_id')->nullable()->constrained('bed_assignments')->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            
            $table->unsignedInteger('systolic_bp')->nullable();
            $table->unsignedInteger('diastolic_bp')->nullable();
            $table->unsignedInteger('heart_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable(); // Fixed
            $table->decimal('weight_kg', 5, 2)->nullable(); // Fixed
            $table->decimal('height_cm', 5, 2)->nullable(); // Fixed
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('patient_vitals'); }
};