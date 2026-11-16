<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsAndFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_models_can_be_created_via_factories_and_relationships_load(): void
    {
        // 1. Core Users & Roles
        $admin = User::factory()->role('admin')->create();
        $this->assertTrue($admin->hasRole('admin'));

        // 2. Department & Doctor & Schedule
        $dept = Department::factory()->create();
        $doctor = Doctor::factory()->for($dept)->create();
        $schedule = DoctorSchedule::factory()->for($doctor)->create();
        $this->assertEquals($doctor->id, $schedule->doctor->id);
        $this->assertTrue($doctor->schedules->contains($schedule));

        // 3. Patient
        $patient = Patient::factory()->create();
        $this->assertNotNull($patient->user);

        // 4. Room & Bed & BedAssignment
        $room = Room::factory()->create();
        $bed = Bed::factory()->for($room)->create();
        $this->assertEquals($room->id, $bed->room->id);
        $this->assertTrue($room->beds->contains($bed));

        $assignment = BedAssignment::factory()
            ->for($patient)
            ->for($bed)
            ->create();
        $this->assertEquals($bed->id, $assignment->bed->id);
        $this->assertEquals($patient->id, $assignment->patient->id);

        // 5. Appointment
        $appointment = Appointment::factory()
            ->for($patient)
            ->for($doctor)
            ->create();
        $this->assertEquals($patient->id, $appointment->patient->id);
        $this->assertEquals($doctor->id, $appointment->doctor->id);

        // 6. Patient Vitals
        $vital = PatientVital::factory()
            ->for($appointment)
            ->create();
        $this->assertEquals($appointment->id, $vital->appointment->id);
        $this->assertNotNull($vital->recorder);
        $this->assertTrue($appointment->vitals->contains($vital));

        // 7. Pharmacy: Medicine, Prescription & Items
        $medicine = Medicine::factory()->create();
        $prescription = Prescription::factory()
            ->for($appointment)
            ->create();
        $item = PrescriptionItem::factory()
            ->for($prescription)
            ->for($medicine)
            ->create();

        $this->assertEquals($prescription->id, $item->prescription->id);
        $this->assertEquals($medicine->id, $item->medicine->id);
        $this->assertTrue($prescription->items->contains($item));
        $this->assertEquals($prescription->id, $appointment->prescription->id);

        // 8. Laboratory: LabTest & LabOrder
        $test = LabTest::factory()->create();
        $order = LabOrder::factory()
            ->for($patient)
            ->for($appointment)
            ->for($test)
            ->completed()
            ->create();

        $this->assertEquals($test->id, $order->test->id);
        $this->assertEquals($patient->id, $order->patient->id);
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertTrue($appointment->labOrders->contains($order));

        // 9. Billing: Invoice, InvoiceItem & Payment
        $invoice = Invoice::factory()
            ->for($patient)
            ->for($appointment)
            ->create(['total_amount' => 1500.00]);

        $invoiceItem = InvoiceItem::factory()
            ->for($invoice)
            ->create([
                'description' => 'Specialist Consultation',
                'item_type' => 'consultation',
                'unit_price' => 1500.00,
                'quantity' => 1,
                'billable_type' => Doctor::class,
                'billable_id' => $doctor->id,
            ]);

        $payment = Payment::factory()
            ->for($invoice)
            ->create(['amount' => 1500.00]);

        $this->assertEquals($invoice->id, $invoiceItem->invoice->id);
        $this->assertEquals($invoice->id, $payment->invoice->id);
        $this->assertTrue($invoice->items->contains($invoiceItem));
        $this->assertTrue($invoice->payments->contains($payment));
        $this->assertTrue($patient->invoices->contains($invoice));
        $this->assertTrue($appointment->invoices->contains($invoice));
    }
}
