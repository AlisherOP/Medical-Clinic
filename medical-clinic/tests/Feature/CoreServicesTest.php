<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\BedAssignmentService;
use App\Services\BillingService;
use App\Services\LabOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CoreServicesTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $appointmentService;

    private LabOrderService $labOrderService;

    private BedAssignmentService $bedService;

    private BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->appointmentService = app(AppointmentService::class);
        $this->labOrderService = app(LabOrderService::class);
        $this->bedService = app(BedAssignmentService::class);
        $this->billingService = app(BillingService::class);
    }

    public function test_appointment_booking_and_slot_overlap_prevention(): void
    {
        $doctor = Doctor::factory()->create();
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();
        $patient3 = Patient::factory()->create();

        $slotTime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);

        // 1. Book first appointment (10:00 - 10:30, duration 30 min)
        $appt1 = $this->appointmentService->book([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $slotTime->toDateTimeString(),
            'duration_minutes' => 30,
        ]);

        $this->assertEquals(1, $appt1->queue_number);
        $this->assertEquals(30, $appt1->duration_minutes);

        // 2. Attempt booking inside the 10:00 - 10:30 window (10:15) - must fail
        $overlapTime = (clone $slotTime)->addMinutes(15);

        $this->expectException(ValidationException::class);
        $this->appointmentService->book([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $overlapTime->toDateTimeString(),
            'duration_minutes' => 15,
        ]);
    }

    public function test_appointment_booking_adjacent_slot_and_queue_sequencing(): void
    {
        $doctor = Doctor::factory()->create();
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        $slotTime1 = now()->addDays(3)->setHour(9)->setMinute(0)->setSecond(0);
        $slotTime2 = (clone $slotTime1)->addMinutes(30); // 09:30

        $appt1 = $this->appointmentService->book([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $slotTime1->toDateTimeString(),
            'duration_minutes' => 30,
        ]);

        $appt2 = $this->appointmentService->book([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'type' => 'consultation',
            'scheduled_at' => $slotTime2->toDateTimeString(),
            'duration_minutes' => 30,
        ]);

        $this->assertEquals(1, $appt1->queue_number);
        $this->assertEquals(2, $appt2->queue_number);

        // Test status flow
        $this->appointmentService->checkIn($appt1);
        $this->assertEquals('waiting', $appt1->fresh()->status);

        $this->appointmentService->startConsultation($appt1);
        $this->assertEquals('in_progress', $appt1->fresh()->status);

        $this->appointmentService->complete($appt1, 'Hypertension', 'Prescribed medication');
        $this->assertEquals('completed', $appt1->fresh()->status);
        $this->assertEquals('Hypertension', $appt1->fresh()->diagnosis);
    }

    public function test_lab_order_lifecycle_and_secure_attachment_storage(): void
    {
        Storage::fake('local');

        $patient = Patient::factory()->create();
        $test = LabTest::factory()->create(['test_name' => 'Complete Blood Count']);
        $tech = User::factory()->role('lab_technician')->create();

        // 1. Order test
        $order = $this->labOrderService->order($patient, $test);
        $this->assertEquals('requested', $order->status);

        // 2. Complete order with PDF attachment
        $fakePdf = UploadedFile::fake()->create('blood_test_results.pdf', 150, 'application/pdf');

        $completedOrder = $this->labOrderService->complete(
            $order,
            $tech,
            'Hemoglobin and platelet levels normal.',
            $fakePdf
        );

        $this->assertEquals('completed', $completedOrder->status);
        $this->assertEquals($tech->id, $completedOrder->performed_by);
        $this->assertNotNull($completedOrder->attachment_url);

        // Verify file stored in private directory
        Storage::disk('local')->assertExists($completedOrder->attachment_url);
    }

    public function test_bed_admission_discharge_and_transfer(): void
    {
        $room = Room::factory()->create(['is_active' => true]);
        $bed1 = Bed::factory()->for($room)->create();
        $bed2 = Bed::factory()->for($room)->create();

        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        // 1. Admit patient 1 into bed 1
        $admitTime = now()->subHours(2);
        $assignment = $this->bedService->admit($patient1, $bed1, null, $admitTime);

        $this->assertEquals('admitted', $assignment->status);
        $this->assertTrue($this->bedService->isBedOccupied($bed1));

        // 2. Cannot admit patient 2 into same occupied bed 1
        try {
            $this->bedService->admit($patient2, $bed1);
            $this->fail('Expected ValidationException when admitting to an occupied bed.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('bed_id', $e->errors());
        }

        // 3. Transfer patient 1 from bed 1 to bed 2
        $newAssignment = $this->bedService->transfer($assignment, $bed2);

        $this->assertEquals('transferred', $assignment->fresh()->status);
        $this->assertEquals('admitted', $newAssignment->status);
        $this->assertFalse($this->bedService->isBedOccupied($bed1));
        $this->assertTrue($this->bedService->isBedOccupied($bed2));

        // 4. Discharge patient 1 from bed 2
        $discharged = $this->bedService->discharge($newAssignment, now());
        $this->assertEquals('discharged', $discharged->status);
        $this->assertFalse($this->bedService->isBedOccupied($bed2));
    }

    public function test_billing_invoice_calculation_and_payments(): void
    {
        $patient = Patient::factory()->create();
        $cashier = User::factory()->role('cashier')->create();

        // 1. Create invoice with 2 items: 1000 + 500 = 1500
        $invoice = $this->billingService->createInvoice(
            $patient,
            null,
            [
                [
                    'description' => 'Doctor Consultation',
                    'item_type' => 'consultation',
                    'unit_price' => 1000.00,
                    'quantity' => 1,
                ],
                [
                    'description' => 'Blood Test',
                    'item_type' => 'lab_test',
                    'unit_price' => 500.00,
                    'quantity' => 1,
                ],
            ],
            100.00 // 100 discount -> payable is 1400.00
        );

        $this->assertEquals(1500.00, (float) $invoice->total_amount);
        $this->assertEquals(100.00, (float) $invoice->discount_amount);
        $this->assertEquals('unpaid', $invoice->status);

        // 2. Partial payment of 600
        $payment1 = $this->billingService->recordPayment($invoice, 600.00, 'cash', $cashier);
        $invoice->refresh();

        $this->assertEquals(600.00, (float) $invoice->paid_amount);
        $this->assertEquals('partially_paid', $invoice->status);

        // 3. Attempt overpayment (payable is 1400 - 600 = 800; try paying 900)
        try {
            $this->billingService->recordPayment($invoice, 900.00, 'credit_card', $cashier);
            $this->fail('Expected ValidationException when overpaying.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->errors());
        }

        // 4. Pay remaining 800 -> status transitions to 'paid'
        $payment2 = $this->billingService->recordPayment($invoice, 800.00, 'credit_card', $cashier);
        $invoice->refresh();

        $this->assertEquals(1400.00, (float) $invoice->paid_amount);
        $this->assertEquals('paid', $invoice->status);
    }
}
