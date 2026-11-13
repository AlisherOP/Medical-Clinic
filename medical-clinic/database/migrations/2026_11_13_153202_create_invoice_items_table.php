<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description', 255);
            $table->enum('item_type', ['consultation', 'medicine', 'lab_test', 'room_charge', 'operation']);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2); // Fixed
            
            // Fixed: Explicit columns instead of nullableMorphs to prevent double indexing
            $table->string('billable_type')->nullable();
            $table->unsignedBigInteger('billable_id')->nullable();
            $table->unique(['billable_type', 'billable_id'], 'unique_billable_item');
            
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_items'); }
};