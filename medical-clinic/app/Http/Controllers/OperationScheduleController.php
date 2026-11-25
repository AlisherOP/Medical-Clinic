<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationScheduleController extends Controller
{
    /**
     * Display the operation schedule dashboard with the 4 real-time top metric bars.
     */
    public function index(Request $request): View|JsonResponse
    {
        $selectedDate = $request->input('date', now()->toDateString());

        // Base query for surgical cases scheduled on the selected date
        $baseQuery = Appointment::where(function ($q) {
            $q->where('type', 'operation')
                ->orWhere('type', 'emergency');
        })->whereDate('scheduled_at', $selectedDate);

        // 1. Today's Caseload (Emergency Priority)
        $totalCaseload = (clone $baseQuery)->count();
        $emergencyCases = (clone $baseQuery)->where(function ($q) {
            $q->where('type', 'emergency')
                ->orWhere('notes', 'like', '%emergency%')
                ->orWhere('diagnosis', 'like', '%emergency%');
        })->count();
        $electiveCases = max(0, $totalCaseload - $emergencyCases);

        // 2. Today's Completed (Pending Active)
        $completedCases = (clone $baseQuery)->where('status', 'completed')->count();
        $activeCases = (clone $baseQuery)->where('status', 'in_progress')->count();
        $pendingCases = (clone $baseQuery)->whereIn('status', ['scheduled', 'waiting'])->count();

        // 3. Today's Cancelled (Audit logs stored safely)
        $cancelledCases = (clone $baseQuery)->where('status', 'cancelled')->count();

        // 4. Theatre Utilization & Average Duration
        $activeTheatresCount = Room::where('type', 'operation_theater')->where('is_active', true)->count();
        if ($activeTheatresCount === 0) {
            $activeTheatresCount = 2; // Default 2 Operating Theatres if not yet seeded
        }

        $totalSurgeriesDuration = (clone $baseQuery)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->sum('duration_minutes');

        $avgDuration = (int) round((clone $baseQuery)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->avg('duration_minutes') ?? 0);

        // Daily standard operating theatre availability: 8 hours (480 mins) per room
        $availableCapacityMinutes = $activeTheatresCount * 480;
        $utilizationRate = $availableCapacityMinutes > 0
            ? min(100.0, round(($totalSurgeriesDuration / $availableCapacityMinutes) * 100, 1))
            : 0.0;

        $metrics = [
            'caseload' => [
                'total' => $totalCaseload,
                'emergency' => $emergencyCases,
                'elective' => $electiveCases,
            ],
            'completed' => [
                'completed' => $completedCases,
                'active' => $activeCases,
                'pending' => $pendingCases,
            ],
            'cancelled' => [
                'cancelled' => $cancelledCases,
                'audit_status' => 'Audit logs stored safely',
            ],
            'theatre_utilization' => [
                'rate' => $utilizationRate,
                'avg_duration_minutes' => $avgDuration,
                'total_booked_minutes' => (int) $totalSurgeriesDuration,
                'active_theatres' => $activeTheatresCount,
            ],
        ];

        // Return real-time JSON feed if requested
        if ($request->wantsJson() || $request->query('feed') === '1') {
            return response()->json([
                'success' => true,
                'date' => $selectedDate,
                'metrics' => $metrics,
            ]);
        }

        return view('operations.index', compact('metrics', 'selectedDate'));
    }
}
