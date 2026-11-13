<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('dosage', 50);
            $table->string('frequency', 50);
            $table->unsignedInteger('duration_days');
            $table->unsignedInteger('quantity');
            $table->text('instructions')->nullable();
            $table->dateTime('dispensed_at')->nullable();
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['prescription_id', 'medicine_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('prescription_items'); }
};