<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->integer('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_patients')->default(20);
            $table->integer('slot_duration_minutes')->default(15);

            $table->unique(['doctor_id', 'day_of_week'], 'unique_doctor_day');
        });

        DB::statement('ALTER TABLE doctor_schedules ADD CONSTRAINT check_day_of_week CHECK (day_of_week BETWEEN 0 AND 6)');
        DB::statement('ALTER TABLE doctor_schedules ADD CONSTRAINT check_times CHECK (start_time < end_time)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};

