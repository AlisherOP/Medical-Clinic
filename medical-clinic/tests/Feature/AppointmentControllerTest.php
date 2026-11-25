<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointments_index_page_loads_successfully(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'scheduled_at' => now()->setHour(10)->setMinute(0)->setSecond(0),
                'status' => 'scheduled',
            ]);

        $response = $this->get('/appointments');

        $response->assertStatus(200);
        $response->assertSee('Live Appointment Queue Board');
        $response->assertSee($patient->user->last_name);
        $response->assertSee($doctor->user->last_name);
    }

    public function test_appointments_json_feed_returns_kpis_and_queue_list(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $today = now()->toDateString();

        Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'scheduled_at' => now()->setHour(9)->setMinute(0)->setSecond(0),
                'status' => 'waiting',
                'queue_number' => 1,
            ]);

        $response = $this->getJson("/appointments?feed=1&date={$today}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'date',
            'kpis' => ['total', 'waiting', 'in_progress', 'completed', 'cancelled'],
            'appointments' => [
                '*' => [
                    'id',
                    'queue_number',
                    'patient_name',
                    'mrn',
                    'scheduled_time',
                    'doctor_name',
                    'department',
                    'status',
                ],
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'kpis' => [
                'total' => 1,
                'waiting' => 1,
            ],
        ]);
    }

    public function test_can_book_new_appointment_via_post(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $bookingTime = now()->addDay()->setHour(11)->setMinute(0)->setSecond(0);

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $bookingTime->toDateTimeString(),
            'duration_minutes' => 20,
            'notes' => 'Patient has headache and mild fever.',
        ];

        $response = $this->postJson('/appointments', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'duration_minutes' => 20,
            'queue_number' => 1,
            'status' => 'scheduled',
        ]);
    }

    public function test_booking_overlapping_slot_fails_with_validation_error(): void
    {
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $bookingTime = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);

        // First appointment: 14:00 - 14:30
        Appointment::factory()
            ->for($patient1)
            ->for($doctor)
            ->create([
                'scheduled_at' => $bookingTime,
                'duration_minutes' => 30,
                'status' => 'scheduled',
            ]);

        // Attempting to book at 14:15 for same doctor
        $overlapTime = (clone $bookingTime)->addMinutes(15);

        $response = $this->postJson('/appointments', [
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $overlapTime->toDateTimeString(),
            'duration_minutes' => 15,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['scheduled_at']);
    }

    public function test_can_check_in_call_in_and_complete_appointment_via_patch(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'scheduled_at' => now(),
                'status' => 'scheduled',
            ]);

        // 1. Check in
        $res1 = $this->patchJson("/appointments/{$appointment->id}/check-in");
        $res1->assertStatus(200);
        $this->assertEquals('waiting', $appointment->fresh()->status);

        // 2. Call in / Start consultation
        $res2 = $this->patchJson("/appointments/{$appointment->id}/start");
        $res2->assertStatus(200);
        $this->assertEquals('in_progress', $appointment->fresh()->status);

        // 3. Complete consultation with diagnosis
        $res3 = $this->patchJson("/appointments/{$appointment->id}/complete", [
            'diagnosis' => 'Acute Pharyngitis',
            'notes' => 'Gargle warm salt water, paracetamol prescribed.',
        ]);
        $res3->assertStatus(200);

        $fresh = $appointment->fresh();
        $this->assertEquals('completed', $fresh->status);
        $this->assertEquals('Acute Pharyngitis', $fresh->diagnosis);
    }

    public function test_can_cancel_appointment_and_records_reason(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'scheduled_at' => now()->addDays(2),
                'status' => 'scheduled',
                'notes' => 'First visit',
            ]);

        $response = $this->patchJson("/appointments/{$appointment->id}/cancel", [
            'reason' => 'Patient called in to reschedule next week.',
        ]);

        $response->assertStatus(200);
        $fresh = $appointment->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertStringContainsString('Cancellation reason: Patient called in to reschedule next week.', $fresh->notes);
    }

    public function test_can_reschedule_appointment_and_reassign_doctor(): void
    {
        $patient = Patient::factory()->create();
        $doctor1 = Doctor::factory()->create();
        $doctor2 = Doctor::factory()->create();

        $appointment = Appointment::factory()
            ->for($patient)
            ->for($doctor1)
            ->create([
                'scheduled_at' => now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0),
                'duration_minutes' => 15,
                'queue_number' => 1,
                'status' => 'scheduled',
            ]);

        $newTime = now()->addDays(3)->setHour(14)->setMinute(0)->setSecond(0);

        $response = $this->patchJson("/appointments/{$appointment->id}/reschedule", [
            'doctor_id' => $doctor2->id,
            'scheduled_at' => $newTime->toDateTimeString(),
            'duration_minutes' => 30,
            'notes' => 'Transferred to specialist doctor2',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $appointment->fresh();
        $this->assertEquals($doctor2->id, $fresh->doctor_id);
        $this->assertEquals(30, $fresh->duration_minutes);
        $this->assertEquals($newTime->format('Y-m-d H:i:s'), $fresh->scheduled_at->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('Transferred to specialist doctor2', $fresh->notes);
    }

    public function test_reschedule_prevents_overlap_for_target_doctor(): void
    {
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $existingTime = now()->addDays(2)->setHour(11)->setMinute(0)->setSecond(0);

        // Existing booking at 11:00 (15 mins)
        Appointment::factory()
            ->for($patient1)
            ->for($doctor)
            ->create([
                'scheduled_at' => $existingTime,
                'duration_minutes' => 15,
                'status' => 'scheduled',
            ]);

        // Second appointment originally booked for another time
        $appointmentToMove = Appointment::factory()
            ->for($patient2)
            ->for($doctor)
            ->create([
                'scheduled_at' => now()->addDays(2)->setHour(15)->setMinute(0)->setSecond(0),
                'duration_minutes' => 15,
                'status' => 'scheduled',
            ]);

        // Attempting to reschedule right over 11:00
        $response = $this->patchJson("/appointments/{$appointmentToMove->id}/reschedule", [
            'doctor_id' => $doctor->id,
            'scheduled_at' => $existingTime->toDateTimeString(),
            'duration_minutes' => 15,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['scheduled_at']);
    }
}
