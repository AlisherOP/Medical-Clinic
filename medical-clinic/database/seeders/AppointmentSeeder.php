<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(AppointmentService::class);

        $doctors = Doctor::with('user')->get();
        $patients = Patient::with('user')->get();
        $receptionist = User::where('role', 'receptionist')->first();

        if ($doctors->isEmpty() || $patients->count() < 6) {
            return;
        }

        $doc1 = $doctors[0];
        $doc2 = $doctors->count() > 1 ? $doctors[1] : $doc1;
        $doc3 = $doctors->count() > 2 ? $doctors[2] : $doc1;

        $today = now();

        // 1. Completed Consultation (09:00 - 09:30)
        $appt1 = $service->book([
            'patient_id' => $patients[0]->id,
            'doctor_id' => $doc1->id,
            'type' => 'consultation',
            'scheduled_at' => (clone $today)->setHour(9)->setMinute(0)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'Patient complained of sore throat and fever for 3 days.',
        ], $receptionist);
        $service->checkIn($appt1);
        $service->startConsultation($appt1);
        $service->complete($appt1, 'Acute Streptococcal Pharyngitis', 'Prescribed Amoxicillin 500mg and rest.');

        // 2. In Consultation Right Now (09:30 - 10:00)
        $appt2 = $service->book([
            'patient_id' => $patients[1]->id,
            'doctor_id' => $doc1->id,
            'type' => 'checkup',
            'scheduled_at' => (clone $today)->setHour(9)->setMinute(30)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'Routine biannual cardiovascular checkup.',
        ], $receptionist);
        $service->checkIn($appt2);
        $service->startConsultation($appt2);

        // 3. Waiting in Lobby (10:00 - 10:30)
        $appt3 = $service->book([
            'patient_id' => $patients[2]->id,
            'doctor_id' => $doc1->id,
            'type' => 'consultation',
            'scheduled_at' => (clone $today)->setHour(10)->setMinute(0)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'Persistent cough and mild shortness of breath.',
        ], $receptionist);
        $service->checkIn($appt3);

        // 4. Waiting in Lobby for Doctor 2 (09:45 - 10:15)
        $appt4 = $service->book([
            'patient_id' => $patients[3]->id,
            'doctor_id' => $doc2->id,
            'type' => 'consultation',
            'scheduled_at' => (clone $today)->setHour(9)->setMinute(45)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'Chronic migraine follow-up consultation.',
        ], $receptionist);
        $service->checkIn($appt4);

        // 5. Scheduled for Later Today (11:00 - 11:30)
        $service->book([
            'patient_id' => $patients[4]->id,
            'doctor_id' => $doc2->id,
            'type' => 'consultation',
            'scheduled_at' => (clone $today)->setHour(11)->setMinute(0)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'Sleep disorder evaluation and test results review.',
        ], $receptionist);

        // 6. Cancelled Appointment (10:30 - 11:00)
        $appt6 = $service->book([
            'patient_id' => $patients[5]->id,
            'doctor_id' => $doc3->id,
            'type' => 'consultation',
            'scheduled_at' => (clone $today)->setHour(10)->setMinute(30)->setSecond(0),
            'duration_minutes' => 30,
            'notes' => 'General pediatric checkup.',
        ], $receptionist);
        $service->cancel($appt6, 'Patient requested reschedule due to vehicle breakdown.');
    }
}
