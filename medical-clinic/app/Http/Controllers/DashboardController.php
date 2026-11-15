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
        $totalPatients = Patient::count();
        $activeAppointments = Appointment::count();
        $activeBeds = BedAssignment::whereNull('discharged_at')->count();

        return view('dashboard', compact('totalPatients', 'activeAppointments', 'activeBeds'));
    }
}