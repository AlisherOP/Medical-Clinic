<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
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
        $criticalPatientsCount = 2;

        $entries = User::latest()->take(10)->get();

        $latestSessions = collect();
        if (Schema::hasTable('sessions')) {
            $latestSessions = DB::table('sessions')
                ->whereNotNull('user_id')
                ->orderByDesc('last_activity')
                ->get()
                ->keyBy('user_id');
        }

        return view('dashboard', compact(
            'totalPatients',
            'activeAppointments',
            'activeBeds',
            'criticalPatientsCount',
            'entries',
            'latestSessions'
        ));
    }
}