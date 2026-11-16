<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchemaModificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_stores_duration_minutes_with_default(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => now()->addDay()->setHour(10)->setMinute(0)->setSecond(0),
            'status' => 'scheduled',
        ]);
        $appointment->queue_number = 1;
        $appointment->save();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'duration_minutes' => 15,
        ]);

        $customAppointment = new Appointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => now()->addDay()->setHour(11)->setMinute(0)->setSecond(0),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);
        $customAppointment->queue_number = 2;
        $customAppointment->save();

        $this->assertDatabaseHas('appointments', [
            'id' => $customAppointment->id,
            'duration_minutes' => 45,
        ]);
    }

    public function test_appointment_rejects_non_positive_duration_minutes(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $this->expectException(QueryException::class);

        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => now()->addDay()->setHour(14)->setMinute(0)->setSecond(0),
            'duration_minutes' => 0,
            'status' => 'scheduled',
        ]);
        $appointment->queue_number = 1;
        $appointment->save();
    }

    public function test_invoice_items_allows_same_billable_entity_on_multiple_rows(): void
    {
        $patient = Patient::factory()->create();

        $invoice1Id = DB::table('invoices')->insertGetId([
            'patient_id' => $patient->id,
            'total_amount' => 1000.00,
            'discount_amount' => 0.00,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoice2Id = DB::table('invoices')->insertGetId([
            'patient_id' => $patient->id,
            'total_amount' => 1000.00,
            'discount_amount' => 0.00,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert same billable entity (e.g. Medicine with ID 10) into both invoices
        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice1Id,
            'description' => 'Paracetamol 500mg',
            'item_type' => 'medicine',
            'quantity' => 2,
            'unit_price' => 50.00,
            'billable_type' => 'App\\Models\\Medicine',
            'billable_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice2Id,
            'description' => 'Paracetamol 500mg',
            'item_type' => 'medicine',
            'quantity' => 1,
            'unit_price' => 50.00,
            'billable_type' => 'App\\Models\\Medicine',
            'billable_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertEquals(
            2,
            DB::table('invoice_items')
                ->where('billable_type', 'App\\Models\\Medicine')
                ->where('billable_id', 10)
                ->count()
        );
    }
}
