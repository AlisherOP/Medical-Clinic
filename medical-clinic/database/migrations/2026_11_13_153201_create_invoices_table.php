<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->restrictOnDelete();
            $table->decimal('total_amount', 10, 2)->default(0.00); // Fixed
            $table->decimal('discount_amount', 10, 2)->default(0.00); // Fixed
            $table->decimal('paid_amount', 10, 2)->default(0.00); // Fixed
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};