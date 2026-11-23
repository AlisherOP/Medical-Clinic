<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BedAssignment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        $fromDate = $fromInput ? Carbon::parse($fromInput)->startOfDay() : null;
        $toDate = $toInput ? Carbon::parse($toInput)->endOfDay() : null;

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        $hasDateFilter = (bool) ($fromDate || $toDate);

        // Determine the best date column on appointments
        $appointmentDateCol = null;
        if (Schema::hasTable('appointments')) {
            foreach (['scheduled_at', 'appointment_date', 'date', 'created_at'] as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $appointmentDateCol = $col;
                    break;
                }
            }
        }

        // Helper to apply date bounds cleanly
        $applyDateRange = function ($query, string $column) use ($fromDate, $toDate) {
            if ($fromDate && $toDate) {
                $query->whereBetween($column, [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->where($column, '>=', $fromDate);
            } elseif ($toDate) {
                $query->where($column, '<=', $toDate);
            }
            return $query;
        };

        // Users / Web Entries query
        $entriesQuery = User::query()->latest();
        if ($hasDateFilter) {
            $applyDateRange($entriesQuery, 'created_at');
        }
        $entries = $entriesQuery->take(10)->get();
        $totalEntriesCount = (clone $entriesQuery)->reorder()->count();

        // Patients count
        $patientsQuery = Patient::query();
        if ($hasDateFilter && Schema::hasColumn('patients', 'created_at')) {
            $applyDateRange($patientsQuery, 'created_at');
        }
        $totalPatients = $patientsQuery->count();

        // Appointments query & operational status breakdown
        $appointmentsQuery = Appointment::query();
        if ($hasDateFilter && $appointmentDateCol) {
            $applyDateRange($appointmentsQuery, $appointmentDateCol);
        }
        $activeAppointments = (clone $appointmentsQuery)->count();

        $completedAppointments = 0;
        $cancelledAppointments = 0;
        $pendingAppointments = 0;

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'status')) {
            $statusCounts = (clone $appointmentsQuery)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->mapWithKeys(fn ($count, $status) => [strtolower((string) $status) => (int) $count]);

            $completedAppointments = ($statusCounts['completed'] ?? 0) + ($statusCounts['done'] ?? 0);
            $cancelledAppointments = ($statusCounts['cancelled'] ?? 0) + ($statusCounts['canceled'] ?? 0);
            $pendingAppointments = ($statusCounts['pending'] ?? 0)
                + ($statusCounts['scheduled'] ?? 0)
                + ($statusCounts['checked_in'] ?? 0)
                + ($statusCounts['in_progress'] ?? 0);
        }

        // Bed assignments count
        $bedsQuery = BedAssignment::query()->whereNull('discharged_at');
        if ($hasDateFilter && Schema::hasColumn('bed_assignments', 'assigned_at')) {
            $applyDateRange($bedsQuery, 'assigned_at');
        } elseif ($hasDateFilter && Schema::hasColumn('bed_assignments', 'created_at')) {
            $applyDateRange($bedsQuery, 'created_at');
        }
        $activeBeds = $bedsQuery->count();

        // Latest sessions map
        $latestSessions = collect();
        if (Schema::hasTable('sessions')) {
            $latestSessions = DB::table('sessions')
                ->whereNotNull('user_id')
                ->orderByDesc('last_activity')
                ->get()
                ->keyBy('user_id');
        }

        $filters = [
            'from' => $fromDate ? $fromDate->toDateString() : '',
            'to' => $toDate ? $toDate->toDateString() : '',
            'active' => $hasDateFilter,
        ];

        return view('dashboard', compact(
            'totalPatients',
            'activeAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'pendingAppointments',
            'activeBeds',
            'entries',
            'totalEntriesCount',
            'latestSessions',
            'filters'
        ));
    }
}