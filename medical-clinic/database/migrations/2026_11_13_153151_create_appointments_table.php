<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('type', ['consultation', 'operation', 'checkup', 'emergency']);
            $table->dateTime('scheduled_at');
            $table->date('appointment_date')->storedAs('DATE(scheduled_at)');
            $table->dateTime('active_slot')->nullable()
                  ->storedAs("IF(status IN ('cancelled','no_show') OR deleted_at IS NOT NULL, NULL, scheduled_at)");
            $table->unsignedInteger('queue_number');
            $table->enum('status', ['scheduled', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['doctor_id', 'active_slot'], 'unique_doctor_active_time');
            $table->unique(['doctor_id', 'appointment_date', 'queue_number'], 'unique_doctor_daily_queue');
            $table->index(['patient_id', 'scheduled_at']);
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('appointments'); }
};