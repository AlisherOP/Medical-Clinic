<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Schedule & Beds
        DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT chk_day_limit CHECK (day_of_week <= 6)");
        DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT chk_start_end_time CHECK (start_time < end_time)");
        DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT chk_max_patients CHECK (max_patients > 0)");
        DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT chk_slot_duration CHECK (slot_duration_minutes > 0)");
        
        DB::statement("ALTER TABLE bed_assignments ADD CONSTRAINT chk_discharge_time CHECK (discharged_at >= admitted_at)");
        DB::statement("ALTER TABLE bed_assignments ADD CONSTRAINT chk_bed_status_consistency CHECK (
            (status = 'admitted' AND discharged_at IS NULL) OR 
            (status <> 'admitted' AND discharged_at IS NOT NULL)
        )");

        // Vitals
        DB::statement("ALTER TABLE patient_vitals ADD CONSTRAINT chk_vitals_owner CHECK (
            appointment_id IS NOT NULL OR bed_assignment_id IS NOT NULL
        )");

        // Clinical / Pharmacy
        DB::statement("ALTER TABLE prescription_items ADD CONSTRAINT chk_prescription_quantity CHECK (quantity > 0)");
        DB::statement("ALTER TABLE prescription_items ADD CONSTRAINT chk_prescription_duration CHECK (duration_days > 0)");
        
        DB::statement("ALTER TABLE lab_orders ADD CONSTRAINT chk_lab_completion CHECK (
            (status = 'completed' AND completed_at IS NOT NULL) OR 
            (status <> 'completed' AND completed_at IS NULL)
        )");

        // Financials (Replacing Laravel 11's deprecated unsignedDecimal natively)
        DB::statement("ALTER TABLE doctors ADD CONSTRAINT chk_doctor_fee CHECK (consultation_fee >= 0)");
        DB::statement("ALTER TABLE rooms ADD CONSTRAINT chk_room_rate CHECK (daily_rate >= 0)");
        DB::statement("ALTER TABLE medicines ADD CONSTRAINT chk_medicine_price CHECK (unit_price >= 0)");
        DB::statement("ALTER TABLE lab_tests ADD CONSTRAINT chk_lab_test_price CHECK (price >= 0)");
        
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoice_amounts CHECK (total_amount >= 0 AND discount_amount >= 0 AND paid_amount >= 0)");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_discount_amount CHECK (discount_amount <= total_amount)");
        
        DB::statement("ALTER TABLE invoice_items ADD CONSTRAINT chk_item_quantity CHECK (quantity > 0)");
        DB::statement("ALTER TABLE invoice_items ADD CONSTRAINT chk_item_price CHECK (unit_price >= 0)");
        
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payment_amount CHECK (amount > 0)");
    }

    public function down(): void
    {
        // Safe for MariaDB & MySQL
        DB::statement("ALTER TABLE doctor_schedules DROP CONSTRAINT chk_day_limit");
        DB::statement("ALTER TABLE doctor_schedules DROP CONSTRAINT chk_start_end_time");
        DB::statement("ALTER TABLE doctor_schedules DROP CONSTRAINT chk_max_patients");
        DB::statement("ALTER TABLE doctor_schedules DROP CONSTRAINT chk_slot_duration");
        
        DB::statement("ALTER TABLE bed_assignments DROP CONSTRAINT chk_discharge_time");
        DB::statement("ALTER TABLE bed_assignments DROP CONSTRAINT chk_bed_status_consistency");
        DB::statement("ALTER TABLE patient_vitals DROP CONSTRAINT chk_vitals_owner");
        
        DB::statement("ALTER TABLE prescription_items DROP CONSTRAINT chk_prescription_quantity");
        DB::statement("ALTER TABLE prescription_items DROP CONSTRAINT chk_prescription_duration");
        DB::statement("ALTER TABLE lab_orders DROP CONSTRAINT chk_lab_completion");
        
        DB::statement("ALTER TABLE doctors DROP CONSTRAINT chk_doctor_fee");
        DB::statement("ALTER TABLE rooms DROP CONSTRAINT chk_room_rate");
        DB::statement("ALTER TABLE medicines DROP CONSTRAINT chk_medicine_price");
        DB::statement("ALTER TABLE lab_tests DROP CONSTRAINT chk_lab_test_price");
        DB::statement("ALTER TABLE invoices DROP CONSTRAINT chk_invoice_amounts");
        DB::statement("ALTER TABLE invoices DROP CONSTRAINT chk_discount_amount");
        DB::statement("ALTER TABLE invoice_items DROP CONSTRAINT chk_item_quantity");
        DB::statement("ALTER TABLE invoice_items DROP CONSTRAINT chk_item_price");
        DB::statement("ALTER TABLE payments DROP CONSTRAINT chk_payment_amount");
    }
};