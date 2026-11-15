<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo logins (password for all: "password"):
     *   admin@clinic.test, receptionist@clinic.test, nurse@clinic.test,
     *   lab@clinic.test, pharmacist@clinic.test, cashier@clinic.test,
     *   doctor@clinic.test, patient@clinic.test
     *
     * Not idempotent (unique emails): re-run with `php artisan migrate:fresh --seed`.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Departments first: doctors reference them
            $departments = collect([
                'General Medicine', 'Cardiology', 'Pediatrics', 'Neurology', 'Orthopedics',
            ])->map(fn ($name) => Department::create(['name' => $name]));

            // Staff with fixed roles and known emails
            $staff = [
                'admin' => ['Admin', 'User', 'admin@clinic.test'],
                'receptionist' => ['Reception', 'Desk', 'receptionist@clinic.test'],
                'nurse' => ['Nurse', 'Joy', 'nurse@clinic.test'],
                'lab_technician' => ['Lab', 'Technician', 'lab@clinic.test'],
                'pharmacist' => ['Pharma', 'Cist', 'pharmacist@clinic.test'],
                'cashier' => ['Cash', 'Desk', 'cashier@clinic.test'],
            ];

            foreach ($staff as $role => [$first, $last, $email]) {
                User::factory()->role($role)->create([
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                ]);
            }

            // Known demo doctor and patient, each with a matching profile row
            Doctor::factory()
                ->for(User::factory()->role('doctor')->state([
                    'first_name' => 'Demo',
                    'last_name' => 'Doctor',
                    'email' => 'doctor@clinic.test',
                ]))
                ->for($departments->first())
                ->create(['specialization' => 'General Practitioner']);

            Patient::factory()
                ->for(User::factory()->role('patient')->state([
                    'first_name' => 'Demo',
                    'last_name' => 'Patient',
                    'email' => 'patient@clinic.test',
                ]))
                ->create();

            // Random doctors spread across the existing departments, and random patients
            Doctor::factory(8)->recycle($departments)->create();
            Patient::factory(30)->create();

            // Add these lines to generate demo appointments and beds
            \App\Models\Appointment::factory(15)->create();
            \App\Models\BedAssignment::factory(5)->create();
        });
    }
}
