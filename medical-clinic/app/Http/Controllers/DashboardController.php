<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BedAssignment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fromInput = $request->input('from');
        $toInput = $request->input('to');
        $searchInput = trim((string) $request->input('search', ''));
        $roleInput = strtolower(trim((string) $request->input('role', 'all')));

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

        // Users / Web Entries query (supports date range + search + role filter)
        $entriesQuery = User::query()->latest();
        if ($hasDateFilter) {
            $applyDateRange($entriesQuery, 'created_at');
        }

        if ($searchInput !== '') {
            $entriesQuery->where(function ($q) use ($searchInput) {
                $like = '%' . $searchInput . '%';
                if (Schema::hasColumn('users', 'full_name')) {
                    $q->orWhere('full_name', 'like', $like);
                }
                if (Schema::hasColumn('users', 'first_name')) {
                    $q->orWhere('first_name', 'like', $like);
                }
                if (Schema::hasColumn('users', 'last_name')) {
                    $q->orWhere('last_name', 'like', $like);
                }
                if (Schema::hasColumn('users', 'name')) {
                    $q->orWhere('name', 'like', $like);
                }
                if (Schema::hasColumn('users', 'email')) {
                    $q->orWhere('email', 'like', $like);
                }
            });
        }

        if ($roleInput !== '' && $roleInput !== 'all' && Schema::hasColumn('users', 'role')) {
            if ($roleInput === 'staff') {
                $entriesQuery->whereIn('role', ['admin', 'doctor', 'nurse', 'receptionist', 'staff']);
            } elseif ($roleInput === 'patient') {
                $entriesQuery->where('role', 'patient');
            } else {
                $entriesQuery->where('role', $roleInput);
            }
        }

        $entries = $entriesQuery->take(15)->get();
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
            'search' => $searchInput,
            'role' => $roleInput ?: 'all',
            'active' => $hasDateFilter || $searchInput !== '' || ($roleInput !== '' && $roleInput !== 'all'),
        ];

        $activeBroadcast = Cache::get('qms_active_broadcast');

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
            'filters',
            'activeBroadcast'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $fromInput = $request->input('from');
        $toInput = $request->input('to');
        $searchInput = trim((string) $request->input('search', ''));
        $roleInput = strtolower(trim((string) $request->input('role', 'all')));

        $fromDate = $fromInput ? Carbon::parse($fromInput)->startOfDay() : null;
        $toDate = $toInput ? Carbon::parse($toInput)->endOfDay() : null;

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        $entriesQuery = User::query()->latest();
        if ($fromDate && $toDate) {
            $entriesQuery->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $entriesQuery->where('created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $entriesQuery->where('created_at', '<=', $toDate);
        }

        if ($searchInput !== '') {
            $entriesQuery->where(function ($q) use ($searchInput) {
                $like = '%' . $searchInput . '%';
                foreach (['full_name', 'first_name', 'last_name', 'name', 'email'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $q->orWhere($col, 'like', $like);
                    }
                }
            });
        }

        if ($roleInput !== '' && $roleInput !== 'all' && Schema::hasColumn('users', 'role')) {
            if ($roleInput === 'staff') {
                $entriesQuery->whereIn('role', ['admin', 'doctor', 'nurse', 'receptionist', 'staff']);
            } elseif ($roleInput === 'patient') {
                $entriesQuery->where('role', 'patient');
            } else {
                $entriesQuery->where('role', $roleInput);
            }
        }

        $entries = $entriesQuery->get();
        $filename = 'qms-dashboard-report-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($entries, $fromInput, $toInput, $roleInput) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['QMS Clinical Operations Report']);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, ['Date Window', ($fromInput ?: 'All') . ' to ' . ($toInput ?: 'Present')]);
            fputcsv($handle, ['Role Filter', strtoupper($roleInput ?: 'all')]);
            fputcsv($handle, []);
            fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Registered At']);

            foreach ($entries as $user) {
                $name = $user->full_name
                    ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    ?: ($user->name ?? 'User #' . $user->id);

                fputcsv($handle, [
                    $user->id,
                    $name,
                    $user->email ?? '',
                    $user->role ?? 'user',
                    $user->created_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:280'],
        ]);

        $user = $request->user();
        $sender = $user?->full_name
            ?? $user?->name
            ?? 'Charge Station';

        $expiresAt = now()->addHours(12);

        Cache::put('qms_active_broadcast', [
            'message' => trim($validated['message']),
            'sender' => $sender,
            'sent_at' => now()->format('H:i'),
            'expires_at' => $expiresAt->format('H:i'),
        ], $expiresAt);

        return redirect()->route('dashboard')->with('status', 'Clinic alert broadcast to all active stations.');
    }

    public function dismissBroadcast()
    {
        Cache::forget('qms_active_broadcast');

        return redirect()->route('dashboard');
    }
}