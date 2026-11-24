<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operation_schedule_page_loads_with_four_metric_bars(): void
    {
        $response = $this->get('/operations');

        $response->assertStatus(200);
        $response->assertSee('Operation Schedule');
        $response->assertSee('Theatre Monitoring');
        $response->assertSee('Caseload');
        $response->assertSee('Completed');
        $response->assertSee('Cancelled');
        $response->assertSee('Theatre Utilization');
        $response->assertSee('Audit logs stored safely');
    }

    public function test_operation_schedule_json_feed_returns_four_realtime_metrics(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        // Operating Theatre
        Room::factory()->create([
            'room_number' => 'OT-01',
            'type' => 'operation_theater',
            'is_active' => true,
        ]);

        $today = now();

        // 1. Completed Emergency Surgery (90 mins)
        Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'type' => 'operation',
                'scheduled_at' => (clone $today)->setHour(8)->setMinute(0)->setSecond(0),
                'duration_minutes' => 90,
                'queue_number' => 1,
                'status' => 'completed',
                'diagnosis' => 'Emergency Appendicitis',
                'notes' => 'Emergency appendectomy completed successfully.',
            ]);

        // 2. In Progress Surgery (60 mins)
        Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'type' => 'operation',
                'scheduled_at' => (clone $today)->setHour(10)->setMinute(0)->setSecond(0),
                'duration_minutes' => 60,
                'queue_number' => 2,
                'status' => 'in_progress',
                'notes' => 'Cholecystectomy elective',
            ]);

        // 3. Scheduled Surgery (30 mins)
        Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'type' => 'operation',
                'scheduled_at' => (clone $today)->setHour(13)->setMinute(0)->setSecond(0),
                'duration_minutes' => 30,
                'queue_number' => 3,
                'status' => 'scheduled',
            ]);

        // 4. Cancelled Surgery
        Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create([
                'type' => 'operation',
                'scheduled_at' => (clone $today)->setHour(15)->setMinute(0)->setSecond(0),
                'duration_minutes' => 45,
                'queue_number' => 4,
                'status' => 'cancelled',
                'notes' => 'Cancellation reason: Patient blood pressure spike.',
            ]);

        $response = $this->getJson('/operations?feed=1&date='.$today->toDateString());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'date',
            'metrics' => [
                'caseload' => ['total', 'emergency', 'elective'],
                'completed' => ['completed', 'active', 'pending'],
                'cancelled' => ['cancelled', 'audit_status'],
                'theatre_utilization' => ['rate', 'avg_duration_minutes', 'total_booked_minutes', 'active_theatres'],
            ],
        ]);

        $data = $response->json('metrics');

        // Assert 1: Caseload
        $this->assertEquals(4, $data['caseload']['total']);
        $this->assertEquals(1, $data['caseload']['emergency']);
        $this->assertEquals(3, $data['caseload']['elective']);

        // Assert 2: Completed / Active / Pending
        $this->assertEquals(1, $data['completed']['completed']);
        $this->assertEquals(1, $data['completed']['active']);
        $this->assertEquals(1, $data['completed']['pending']);

        // Assert 3: Cancelled
        $this->assertEquals(1, $data['cancelled']['cancelled']);
        $this->assertEquals('Audit logs stored safely', $data['cancelled']['audit_status']);

        // Assert 4: Utilization & Duration
        $this->assertGreaterThan(0, $data['theatre_utilization']['rate']);
        $this->assertEquals(60, $data['theatre_utilization']['avg_duration_minutes']); // (90 + 60 + 30) / 3 = 60
    }

    public function test_can_switch_tabs_between_appointments_and_operations(): void
    {
        // 1. Visit appointments: link to operations exists and appointments tab is active
        $resAppt = $this->get('/appointments');
        $resAppt->assertStatus(200);
        $resAppt->assertSee('Operation Schedule');
        $resAppt->assertSee('Appointments');

        // 2. Visit operations: link to appointments exists and operations tab is active
        $resOp = $this->get('/operations');
        $resOp->assertStatus(200);
        $resOp->assertSee('Operation Schedule');
        $resOp->assertSee('Appointments');
    }
}
