<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Display the live appointment queue board.
     */
    public function index(Request $request): View|JsonResponse
    {
        $selectedDate = $request->input('date', now()->toDateString());
        $doctorId = $request->input('doctor_id');
        $status = $request->input('status');

        $query = Appointment::with(['patient.user', 'doctor.user', 'doctor.department'])
            ->whereDate('scheduled_at', $selectedDate)
            ->orderBy('scheduled_at')
            ->orderBy('queue_number');

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $appointments = $query->get();

        // Compute queue telemetry (wait time and patients ahead) per doctor
        $inProgressByDoctor = $appointments->where('status', 'in_progress')->groupBy('doctor_id');
        $waitingByDoctor = $appointments->where('status', 'waiting')->groupBy('doctor_id');

        $calcWaitTelemetry = function (Appointment $appt) use ($inProgressByDoctor, $waitingByDoctor) {
            $docId = $appt->doctor_id;
            $hasInProgress = isset($inProgressByDoctor[$docId]) && $inProgressByDoctor[$docId]->isNotEmpty();
            $waitingList = $waitingByDoctor[$docId] ?? collect();

            if ($appt->status === 'in_progress') {
                return [
                    'position' => 0,
                    'patients_ahead' => 0,
                    'wait_text' => 'In Consultation',
                    'wait_minutes' => 0,
                ];
            }

            if ($appt->status === 'waiting') {
                $aheadCount = $waitingList->filter(function ($other) use ($appt) {
                    return $other->queue_number < $appt->queue_number;
                })->count() + ($hasInProgress ? 1 : 0);

                $minutes = max(5, $aheadCount * ($appt->duration_minutes ?: 15));

                return [
                    'position' => $aheadCount + 1,
                    'patients_ahead' => $aheadCount,
                    'wait_text' => $aheadCount === 0 ? 'Next in Line' : "~{$minutes}m ({$aheadCount} ahead)",
                    'wait_minutes' => $minutes,
                ];
            }

            if ($appt->status === 'scheduled') {
                return [
                    'position' => null,
                    'patients_ahead' => null,
                    'wait_text' => 'Scheduled',
                    'wait_minutes' => null,
                ];
            }

            return [
                'position' => null,
                'patients_ahead' => null,
                'wait_text' => ucfirst($appt->status),
                'wait_minutes' => 0,
            ];
        };

        // Attach telemetry to each appointment for the blade view
        $appointments->each(function (Appointment $appt) use ($calcWaitTelemetry) {
            $appt->setAttribute('telemetry', $calcWaitTelemetry($appt));
        });

        // Compute KPIs for today's board
        $kpiQuery = Appointment::whereDate('scheduled_at', $selectedDate);
        if ($doctorId) {
            $kpiQuery->where('doctor_id', $doctorId);
        }

        $kpis = [
            'total' => (clone $kpiQuery)->count(),
            'waiting' => (clone $kpiQuery)->where('status', 'waiting')->count(),
            'in_progress' => (clone $kpiQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $kpiQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $kpiQuery)->where('status', 'cancelled')->count(),
        ];

        // Format appointments for dynamic JSON feed
        if ($request->wantsJson() || $request->query('feed') === '1') {
            $data = $appointments->map(function (Appointment $appt) use ($calcWaitTelemetry) {
                // Extract cancellation reason from notes if present
                $cancellationReason = '';
                if ($appt->status === 'cancelled' && $appt->notes) {
                    if (preg_match('/Cancellation reason:\s*(.*)/i', $appt->notes, $matches)) {
                        $cancellationReason = trim($matches[1]);
                    } else {
                        $cancellationReason = $appt->notes;
                    }
                }

                $telemetry = $calcWaitTelemetry($appt);

                return [
                    'id' => $appt->id,
                    'queue_number' => sprintf('%02d', $appt->queue_number),
                    'patient_name' => $appt->patient?->user?->full_name ?? 'Unknown Patient',
                    'mrn' => $appt->patient?->mrn ?? 'N/A',
                    'scheduled_time' => Carbon::parse($appt->scheduled_at)->format('H:i'),
                    'scheduled_date' => Carbon::parse($appt->scheduled_at)->toDateString(),
                    'scheduled_datetime' => Carbon::parse($appt->scheduled_at)->format('Y-m-d\TH:i'),
                    'duration_minutes' => $appt->duration_minutes ?? 15,
                    'doctor_id' => $appt->doctor_id,
                    'doctor_name' => 'Dr. '.($appt->doctor?->user?->last_name ?? 'Doctor'),
                    'department' => $appt->doctor?->department?->name ?? 'General',
                    'consultation_room' => $appt->doctor?->consultation_room ?? 'Suite 101',
                    'wait_text' => $telemetry['wait_text'],
                    'patients_ahead' => $telemetry['patients_ahead'],
                    'wait_minutes' => $telemetry['wait_minutes'],
                    'type' => ucfirst($appt->type),
                    'status' => $appt->status,
                    'cancellation_reason' => $cancellationReason,
                    'diagnosis' => $appt->diagnosis,
                    'notes' => $appt->notes,
                ];
            });

            return response()->json([
                'success' => true,
                'date' => $selectedDate,
                'kpis' => $kpis,
                'appointments' => $data,
            ]);
        }

        $doctors = Doctor::with(['user', 'department'])->get();
        $departments = Department::all();
        $patients = Patient::with('user')->get();

        return view('appointments.index', compact(
            'appointments',
            'doctors',
            'departments',
            'patients',
            'selectedDate',
            'doctorId',
            'status',
            'kpis'
        ));
    }

    /**
     * Book a new appointment via the service.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse|RedirectResponse
    {
        $appointment = $this->appointmentService->book($request->validated(), auth()->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully.',
                'appointment' => $appointment->load(['patient.user', 'doctor.user']),
            ], 201);
        }

        return redirect()->route('appointments.index')
            ->with('success', "Ticket #{$appointment->queue_number} booked successfully for {$appointment->patient->user->full_name}.");
    }

    /**
     * Mark patient as arrived and waiting in queue.
     */
    public function checkIn(Appointment $appointment): JsonResponse
    {
        $updated = $this->appointmentService->checkIn($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Patient checked in successfully.',
            'status' => $updated->status,
        ]);
    }

    /**
     * Call patient into consultation room.
     */
    public function start(Appointment $appointment): JsonResponse
    {
        $updated = $this->appointmentService->startConsultation($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Consultation started.',
            'status' => $updated->status,
        ]);
    }

    /**
     * Complete consultation with diagnosis and clinical notes.
     */
    public function complete(Request $request, Appointment $appointment): JsonResponse
    {
        $request->validate([
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $updated = $this->appointmentService->complete(
            $appointment,
            $request->input('diagnosis'),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Consultation completed.',
            'status' => $updated->status,
        ]);
    }

    /**
     * Cancel appointment and record reason.
     */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $updated = $this->appointmentService->cancel($appointment, $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'status' => $updated->status,
        ]);
    }

    /**
     * Reschedule appointment and optionally reassign doctor.
     */
    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = $this->appointmentService->reschedule($appointment, $validated);

        return response()->json([
            'success' => true,
            'message' => "Appointment rescheduled to {$updated->scheduled_at->format('M d, Y H:i')}.",
            'appointment' => $updated->load(['patient.user', 'doctor.user']),
        ]);
    }
}
