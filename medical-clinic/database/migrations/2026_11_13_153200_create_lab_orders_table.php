<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->restrictOnDelete();
            $table->foreignId('lab_test_id')->constrained('lab_tests')->restrictOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('status', ['requested', 'in_progress', 'completed', 'cancelled'])->default('requested');
            $table->text('result_summary')->nullable();
            $table->string('attachment_url', 255)->nullable();
            $table->dateTime('ordered_at')->useCurrent();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lab_orders'); }
};