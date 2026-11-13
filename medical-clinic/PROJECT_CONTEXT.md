# Medical Clinic — Project Context (for an AI without folder access)

Snapshot as of 2026-11-13, commit `a3c0094` on `main`
(GitHub: https://github.com/AlisherOP/Medical-Clinic).

## 1. What this is

A university group project: a **clinic/hospital management system** built with Laravel.
Planned features: patients, doctors/departments, doctor schedules, appointments with a daily
queue, in-patient beds, vitals, prescriptions and pharmacy dispensing, lab tests/orders with
result files, invoicing and payments.

**Current status: database layer only.** The schema (migrations), Eloquent models, factories
and a demo seeder exist. There are **no controllers, routes (except the default welcome page),
views, authentication, authorization/policies, or form validation yet.** Nothing user-facing
has been built.

## 2. Stack and environment

- PHP 8.5, **Laravel 13** (framework 13.42), Composer 2.9
- Dev dependencies: Faker, PHPUnit 12, Pint, Pail, Mockery, Collision. No Breeze/Jetstream/Livewire/Inertia installed.
- Front end: default Laravel Vite setup (`resources/css/app.css`, `resources/js/app.js`), default `welcome.blade.php`.
- **Database: MySQL 8.4** (local, XAMPP-style). `.env`: `DB_CONNECTION=mysql`, database `medical_clinic`, user `medical_admin`.
  The migrations use **MySQL-only SQL** (generated columns with `IF()`, `ALTER TABLE ... ADD CONSTRAINT CHECK`), so they do not run on SQLite.
- App timezone: `UTC` (`config/app.php`). MySQL server runs on system time (UTC+6, Kyrgyzstan). See Known Issues.
- Sessions, cache and queue all use the `database` driver.
- Locale `en`; phone numbers formatted `+996 ### ### ###` (Kyrgyzstan).

### Repo layout
The git root is the **parent folder** `Medical Clinic/`, which contains:
- `medical-clinic/` — the Laravel app (everything below refers to this)
- `schema.sql` — an OLD, outdated PostgreSQL draft of the schema. **Ignore it; migrations are the source of truth.**
- `README.txt`, `.RData`, `.Rhistory` — junk / unrelated.

Relevant app files:
```
app/Models/        User, Department, Patient, Doctor, Appointment, Room, Bed, BedAssignment
database/migrations/   21 migrations (3 Laravel defaults + 18 domain)
database/factories/    UserFactory, DepartmentFactory, PatientFactory, DoctorFactory
database/seeders/      DatabaseSeeder
routes/web.php         only GET / -> welcome view
public/uploads/lab_results/   intended for lab result files (.htaccess blocks script execution)
tests/                 only the two default example tests
```

## 3. Users and roles

Single `users` table for everyone. `users.role` is an ENUM:
`patient, doctor, admin, receptionist, nurse, lab_technician, pharmacist, cashier`.

- A `patient` user has one row in `patients` (profile). A `doctor` user has one row in `doctors`.
- Staff roles (admin, receptionist, nurse, lab_technician, pharmacist, cashier) have no profile table.
- The DB does **not** enforce that role and profile match; the factories/seeder do.
- `users` has no `name` column — it uses `first_name` + `last_name` (any starter kit must be adapted).
- No `email_verified_at` column.

**Security rule:** `role` is intentionally **not** in `User::$fillable`, so it can't be mass-assigned
from a form (prevents self-registration as admin). Set it explicitly:
```php
$user = new User($request->validated());
$user->role = 'patient';   // public registration is always patient
$user->save();
```
Only an admin-only page should assign staff roles.

## 4. Database schema (all tables)

Conventions: `id` = BIGINT unsigned auto-increment PK; `timestamps` = `created_at`/`updated_at`;
`softDeletes` = `deleted_at`. Money = `DECIMAL(10,2)`. Nearly all foreign keys are
**RESTRICT on delete** (medical records are never cascade-deleted); exceptions noted.

### users
first_name(100), last_name(100), email(150, unique), password, phone(20, null),
role ENUM(see above), date_of_birth DATE null, gender ENUM(male,female,other) null,
remember_token, timestamps, softDeletes.
(Also default Laravel tables: password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs.)

### departments
name(100, unique), timestamps.

### patients
user_id → users (unique, restrict), mrn(50, unique — Medical Record Number),
blood_group ENUM(A+,A-,B+,B-,AB+,AB-,O+,O-) null, allergies TEXT null,
emergency_contact_name(150) null, emergency_contact_phone(20) null,
insurance_provider(100) null, insurance_policy_number(50) null, timestamps, softDeletes.

### doctors
user_id → users (unique, restrict), department_id → departments (restrict),
specialization(100), license_number(50, unique), consultation_fee DECIMAL default 0
(CHECK ≥ 0), timestamps, softDeletes.

### doctor_schedules
doctor_id → doctors (**cascade**), day_of_week TINYINT (0=Sunday … 6=Saturday, CHECK ≤ 6),
start_time TIME, end_time TIME (CHECK start < end), max_patients default 20 (CHECK > 0),
slot_duration_minutes default 15 (CHECK > 0), timestamps.

### appointments
patient_id → patients, doctor_id → doctors, created_by → users (null),
type ENUM(consultation, operation, checkup, emergency), scheduled_at DATETIME,
**appointment_date** DATE = generated STORED `DATE(scheduled_at)`,
**active_slot** DATETIME = generated STORED: NULL if status is cancelled/no_show or row soft-deleted, else scheduled_at,
queue_number INT unsigned NOT NULL,
status ENUM(scheduled, waiting, in_progress, completed, cancelled, no_show) default scheduled,
diagnosis TEXT null, notes TEXT null, timestamps, softDeletes.
Indexes: UNIQUE(doctor_id, active_slot) — a doctor can't have two live appointments at the exact same time;
UNIQUE(doctor_id, appointment_date, queue_number) — per-doctor daily queue numbers;
INDEX(patient_id, scheduled_at); INDEX(status).

### rooms
room_number(20, unique), type ENUM(general_ward, icu, operation_theater, private),
daily_rate DECIMAL (CHECK ≥ 0), is_active BOOL default true, timestamps.

### beds
room_id → rooms (**cascade**), bed_number(20), UNIQUE(room_id, bed_number), timestamps.

### bed_assignments (admissions)
patient_id → patients, bed_id → beds, appointment_id → appointments (null),
admitted_at DATETIME default CURRENT_TIMESTAMP, discharged_at DATETIME null,
status ENUM(admitted, discharged, transferred) default admitted,
**active_bed_id** = generated STORED `IF(discharged_at IS NULL, bed_id, NULL)` — UNIQUE (one patient per bed),
**active_patient_id** = generated STORED `IF(discharged_at IS NULL, patient_id, NULL)` — UNIQUE (one bed per patient),
timestamps.
CHECKs: discharged_at ≥ admitted_at; status='admitted' ⇔ discharged_at IS NULL.

### patient_vitals
appointment_id → appointments (null), bed_assignment_id → bed_assignments (null),
recorded_by → users, systolic_bp, diastolic_bp, heart_rate (unsigned ints, null),
temperature DECIMAL(4,1), weight_kg DECIMAL(5,2), height_cm DECIMAL(5,2) (all null), timestamps.
CHECK: at least one of appointment_id / bed_assignment_id is set.

### medicines
name(150), generic_name(150) null, category(100) null, stock_quantity INT unsigned default 0,
unit(20) default 'tablets', unit_price DECIMAL (CHECK ≥ 0), expiry_date DATE null, timestamps.

### prescriptions
appointment_id → appointments (unique — one prescription per appointment), notes TEXT null, timestamps.
(The diagnosis lives on `appointments.diagnosis`.)

### prescription_items
prescription_id → prescriptions (**cascade**), medicine_id → medicines,
dosage(50), frequency(50), duration_days (CHECK > 0), quantity (CHECK > 0), instructions TEXT null,
dispensed_at DATETIME null, dispensed_by → users (null), timestamps.
UNIQUE(prescription_id, medicine_id).

### lab_tests (catalogue)
test_name(150), code(50, unique), category(100) null, price DECIMAL (CHECK ≥ 0),
turnaround_time_hours default 24, timestamps.

### lab_orders
patient_id → patients, appointment_id → appointments (null), lab_test_id → lab_tests,
performed_by → users (null), status ENUM(requested, in_progress, completed, cancelled) default requested,
result_summary TEXT null, attachment_url(255) null, ordered_at DATETIME default now, completed_at null, timestamps.
CHECK: status='completed' ⇔ completed_at IS NOT NULL.

### invoices
patient_id → patients, appointment_id → appointments (null — walk-in pharmacy/lab sales),
total_amount, discount_amount, paid_amount (DECIMAL, default 0, all ≥ 0, discount ≤ total),
status ENUM(unpaid, partially_paid, paid, refunded) default unpaid, timestamps.

### invoice_items
invoice_id → invoices (**cascade**), description(255),
item_type ENUM(consultation, medicine, lab_test, room_charge, operation),
quantity default 1 (CHECK > 0), unit_price DECIMAL (CHECK ≥ 0),
billable_type(string, null) + billable_id(bigint, null) — polymorphic link to the billed thing (no FK),
UNIQUE(billable_type, billable_id), timestamps.

### payments
invoice_id → invoices, received_by → users, type ENUM(payment, refund) default payment,
amount DECIMAL (CHECK > 0), payment_method ENUM(cash, credit_card, insurance, bank_transfer),
transaction_reference(100) null, timestamps.

### Relationship summary
```
User 1─1 Patient          User 1─1 Doctor ─* Department(1)
Patient 1─* Appointment *─1 Doctor          Doctor 1─* DoctorSchedule
Appointment 1─1 Prescription 1─* PrescriptionItem *─1 Medicine
Appointment 1─* LabOrder *─1 LabTest        (LabOrder also → Patient)
Room 1─* Bed 1─* BedAssignment *─1 Patient  (BedAssignment → Appointment optional)
PatientVitals → Appointment or BedAssignment
Patient 1─* Invoice 1─* InvoiceItem ; Invoice 1─* Payment
```

## 5. Eloquent models (app/Models)

Style: classic `protected $fillable` arrays, `casts()` method, typed relation return types, `HasFactory`.

| Model | Traits | Fillable | Relations / extras |
|---|---|---|---|
| User | HasFactory, Notifiable, SoftDeletes | first_name, last_name, email, password, phone, date_of_birth, gender (**not role**) | patient() hasOne, doctor() hasOne, `hasRole(...$roles)`, `full_name` accessor; casts password=hashed, date_of_birth=date |
| Department | HasFactory | name | doctors() |
| Patient | HasFactory, SoftDeletes | mrn, blood_group, allergies, emergency_contact_*, insurance_* (**not user_id**) | user(), appointments(), bedAssignments() |
| Doctor | HasFactory, SoftDeletes | department_id, specialization, license_number, consultation_fee (**not user_id**) | user(), department(), appointments(); fee cast decimal:2 |
| Appointment | HasFactory, SoftDeletes | patient_id, doctor_id, type, scheduled_at, status, diagnosis, notes | patient(), doctor(), creator() (created_by), bedAssignments() |
| Room | HasFactory | room_number, type, daily_rate, is_active | beds() |
| Bed | HasFactory | room_id, bed_number | room(), assignments(), currentAssignment() (where discharged_at null) |
| BedAssignment | — | patient_id, bed_id, appointment_id, admitted_at, discharged_at, status | patient(), bed(), appointment() |

Deliberately **not fillable**:
- Generated columns (MySQL rejects writes): `appointment_date`, `active_slot`, `active_bed_id`, `active_patient_id`.
- Server-assigned: `appointments.queue_number`, `appointments.created_by`, `patients.user_id`, `doctors.user_id`
  (create profiles via the relation: `$user->patient()->create([...])`).

**Models not yet created:** DoctorSchedule, PatientVital, Medicine, Prescription, PrescriptionItem,
LabTest, LabOrder, Invoice, InvoiceItem, Payment. (No factories for Room/Bed/Appointment either —
Room/Bed/Appointment use HasFactory but have no factory class yet.)

## 6. Factories and seeder

- `UserFactory`: role defaults to `patient`; phone `+996 ### ### ###` (fixed length — `fake()->phoneNumber()` can exceed 20 chars); password `password`; `->role('doctor')` state helper.
- `PatientFactory`: auto-creates a `patient`-role user; MRN `MRN-######`.
- `DoctorFactory`: auto-creates a `doctor`-role user and a department; license `LIC-######`; specialization derived from the department name (Cardiology → Cardiologist, etc.).
- `DepartmentFactory`: unique name from a fixed list of 12.
- `DatabaseSeeder` (one transaction): 5 departments (General Medicine, Cardiology, Pediatrics, Neurology, Orthopedics) → 6 staff with fixed roles → demo doctor + demo patient → 8 random doctors spread over the departments → 30 random patients. Not idempotent; re-run with `php artisan migrate:fresh --seed`.

**Demo logins** (password `password` for all — login UI doesn't exist yet):
admin@clinic.test, receptionist@clinic.test, nurse@clinic.test, lab@clinic.test,
pharmacist@clinic.test, cashier@clinic.test, doctor@clinic.test, patient@clinic.test.

## 7. Commands
```
php artisan migrate:fresh --seed   # rebuild + seed (MySQL)
php artisan serve                  # http://localhost:8000
php artisan test                   # only 2 example tests; none touch the DB
npm install && npm run dev         # Vite assets
```

## 8. Known issues / open work (not yet fixed)

Critical:
1. **Timezone mismatch.** App writes UTC; MySQL `DEFAULT CURRENT_TIMESTAMP` columns (`admitted_at`, `ordered_at`) get server time (UTC+6). Discharging within ~6 h of an admission that used the DB default violates `discharged_at ≥ admitted_at`. `appointment_date` is computed from UTC, so local 00:00–05:59 bookings land on the previous day. Fix: set the DB connection `timezone` to match the app (or set the app to `Asia/Bishkek`) and always write timestamps from the app.
2. **Double-booking possible.** Uniqueness is only on the exact minute; 10:00 and 10:05 with the same doctor are both accepted. Appointments have no duration/end time. Needs an overlap check in the booking service under a lock (e.g. `lockForUpdate` on the doctor's day).
3. **DB tests can't run.** `phpunit.xml` uses SQLite `:memory:`, but migrations are MySQL-only (and pdo_sqlite isn't installed). Use a separate MySQL test database.
4. **Lab result files under `public/`** would be downloadable by anyone with the URL. Store in `storage/app/private` and serve through an authorized route.

High:
5. `lab_orders`, `invoices`, `bed_assignments` store both `patient_id` and `appointment_id`; nothing ensures they refer to the same patient.
6. `UNIQUE(billable_type, billable_id)` on invoice_items means a billable thing can be invoiced only once ever (breaks daily room charges / re-billing).
7. `doctor_schedules` has no UNIQUE(doctor_id, day_of_week) or overlap protection.
8. `invoices.paid_amount`/`status` are stored separately from `payments` and can drift.
9. Role ↔ profile consistency is not enforced by the DB.
10. Soft-deleted users/patients still block their email/MRN (plain UNIQUE).

Medium:
11. `queue_number` (NOT NULL, unique per doctor/day) needs race-safe assignment (lock + max+1, or retry).
12. `dispensed_at`/`dispensed_by` can be inconsistent; stock is never decremented.
13. Same medicine can't appear twice on one prescription; one expiry date per medicine (no batches).
14. No `ordered_by` on lab orders; no vitals sanity checks; no audit log; sensitive fields (allergies, insurance, diagnosis) unencrypted.

Presentation wording: say "designed with production practices in mind", not "production-ready" — a real hospital system would also need audit logs, encryption of medical data and backups.

## 9. Suggested next steps
1. Fix the timezone config and add the missing schema constraints (new migrations, don't edit old ones once shared).
2. Add the remaining models.
3. Auth (e.g. Breeze adapted to first_name/last_name; registration always sets role=patient), role middleware/policies.
4. Booking service (overlap check + queue number under a lock), then controllers/views per role:
   receptionist (patients, appointments), doctor (queue, diagnosis, prescriptions, lab orders), nurse (vitals, beds),
   lab (orders/results), pharmacist (dispensing, stock), cashier (invoices/payments), admin (staff, departments, rooms).
5. Feature tests against a MySQL test database.
