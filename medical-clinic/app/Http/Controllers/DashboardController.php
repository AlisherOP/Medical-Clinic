<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\BedAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        // Core KPI Metrics
        $totalPatients = Patient::count();
        $activeAppointments = Appointment::count();
        $activeBeds = BedAssignment::whereNull('discharged_at')->count();
        
        // Safe temporary fallback until the team adds the 'triage_priority' migration column
        $criticalPatientsCount = 2;

        // Data for the Live Patient Care Registry table
        $patients = Patient::latest()->take(10)->get();

        return view('dashboard', compact(
            'totalPatients', 
            'activeAppointments', 
            'activeBeds', 
            'criticalPatientsCount',
            'patients'
        ));
    }
}