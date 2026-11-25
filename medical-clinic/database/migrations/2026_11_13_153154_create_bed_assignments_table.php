<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bed_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('bed_id')->constrained('beds')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->restrictOnDelete(); // Fixed to restrict
            $table->dateTime('admitted_at')->useCurrent();
            $table->dateTime('discharged_at')->nullable();
            $table->enum('status', ['admitted', 'discharged', 'transferred'])->default('admitted');
            
            $table->unsignedBigInteger('active_bed_id')->nullable()->storedAs("CASE WHEN discharged_at IS NULL THEN bed_id ELSE NULL END");
            $table->unique('active_bed_id', 'unique_active_bed');
            
            $table->unsignedBigInteger('active_patient_id')->nullable()->storedAs("CASE WHEN discharged_at IS NULL THEN patient_id ELSE NULL END");
            $table->unique('active_patient_id', 'unique_active_patient');

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bed_assignments'); }
};