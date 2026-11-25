<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Book a new appointment safely with overlap prevention and race-safe queue numbering.
     *
     * @param  array{
     *     patient_id: int,
     *     doctor_id: int,
     *     type: string,
     *     scheduled_at: string|\DateTimeInterface,
     *     duration_minutes?: int,
     *     diagnosis?: ?string,
     *     notes?: ?string
     * }  $data
     *
     * @throws ValidationException
     */
    public function book(array $data, ?User $createdBy = null): Appointment
    {
        return DB::transaction(function () use ($data, $createdBy) {
            // Lock the doctor's record to serialize concurrent bookings for this doctor
            $doctor = Doctor::where('id', $data['doctor_id'])->lockForUpdate()->firstOrFail();

            $start = Carbon::parse($data['scheduled_at']);
            $duration = (int) ($data['duration_minutes'] ?? 15);

            if ($duration <= 0) {
                throw ValidationException::withMessages([
                    'duration_minutes' => 'Appointment duration must be at least 1 minute.',
                ]);
            }

            $end = (clone $start)->addMinutes($duration);

            // Check for overlapping active appointments for this doctor
            $overlap = Appointment::where('doctor_id', $doctor->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->where(function ($query) use ($start, $end) {
                    $query->whereRaw(
                        'scheduled_at < ? AND DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?',
                        [$end->toDateTimeString(), $start->toDateTimeString()]
                    );
                })
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'The selected doctor already has an active appointment during this time window.',
                ]);
            }

            // Atomically compute next queue number for this doctor on this day
            $queueNumber = (int) (Appointment::where('doctor_id', $doctor->id)
                ->whereDate('scheduled_at', $start->toDateString())
                ->max('queue_number') ?? 0) + 1;

            $appointment = new Appointment([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $doctor->id,
                'type' => $data['type'] ?? 'consultation',
                'scheduled_at' => $start,
                'duration_minutes' => $duration,
                'status' => 'scheduled',
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $appointment->queue_number = $queueNumber;
            if ($createdBy) {
                $appointment->creator()->associate($createdBy);
            }

            $appointment->save();

            return $appointment;
        });
    }

    /**
     * Reschedule an appointment and/or reassign doctor safely with overlap prevention.
     *
     * @param  array{
     *     doctor_id: int,
     *     scheduled_at: string|\DateTimeInterface,
     *     duration_minutes?: int,
     *     notes?: ?string
     * }  $data
     *
     * @throws ValidationException
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        if (in_array($appointment->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot reschedule an appointment in '{$appointment->status}' status.",
            ]);
        }

        return DB::transaction(function () use ($appointment, $data) {
            $targetDoctorId = (int) $data['doctor_id'];
            $doctor = Doctor::where('id', $targetDoctorId)->lockForUpdate()->firstOrFail();

            $start = Carbon::parse($data['scheduled_at']);
            $duration = (int) ($data['duration_minutes'] ?? $appointment->duration_minutes ?? 15);

            if ($duration <= 0) {
                throw ValidationException::withMessages([
                    'duration_minutes' => 'Appointment duration must be at least 1 minute.',
                ]);
            }

            $end = (clone $start)->addMinutes($duration);

            // Check for overlapping active appointments for target doctor, excluding this appointment
            $overlap = Appointment::where('doctor_id', $doctor->id)
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->where(function ($query) use ($start, $end) {
                    $query->whereRaw(
                        'scheduled_at < ? AND DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?',
                        [$end->toDateTimeString(), $start->toDateTimeString()]
                    );
                })
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'The selected doctor already has an active appointment during this time window.',
                ]);
            }

            // Recalculate queue number if doctor or date changed
            $currentDate = Carbon::parse($appointment->scheduled_at)->toDateString();
            $newDate = $start->toDateString();
            $queueNumber = $appointment->queue_number;

            if ($appointment->doctor_id !== $doctor->id || $currentDate !== $newDate) {
                $queueNumber = (int) (Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('scheduled_at', $newDate)
                    ->where('id', '!=', $appointment->id)
                    ->max('queue_number') ?? 0) + 1;
            }

            $notes = $appointment->notes;
            if (! empty($data['notes'])) {
                $notes = $notes ? "{$notes}\nRescheduled: {$data['notes']}" : "Rescheduled: {$data['notes']}";
            }

            $appointment->update([
                'doctor_id' => $doctor->id,
                'scheduled_at' => $start,
                'duration_minutes' => $duration,
                'queue_number' => $queueNumber,
                'status' => 'scheduled',
                'notes' => $notes,
            ]);

            return $appointment;
        });
    }

    /**
     * Mark patient as arrived and waiting in queue.
     */
    public function checkIn(Appointment $appointment): Appointment
    {
        if ($appointment->status !== 'scheduled') {
            throw ValidationException::withMessages([
                'status' => "Cannot check in appointment currently in '{$appointment->status}' status.",
            ]);
        }

        $appointment->update(['status' => 'waiting']);

        return $appointment;
    }

    /**
     * Begin consultation with doctor.
     */
    public function startConsultation(Appointment $appointment): Appointment
    {
        if (! in_array($appointment->status, ['scheduled', 'waiting'], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot start consultation for an appointment in '{$appointment->status}' status.",
            ]);
        }

        $appointment->update(['status' => 'in_progress']);

        return $appointment;
    }

    /**
     * Complete consultation with diagnosis and notes.
     */
    public function complete(Appointment $appointment, ?string $diagnosis = null, ?string $notes = null): Appointment
    {
        if ($appointment->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Cannot complete an appointment that has already been cancelled.',
            ]);
        }

        $updates = ['status' => 'completed'];

        if ($diagnosis !== null) {
            $updates['diagnosis'] = $diagnosis;
        }

        if ($notes !== null) {
            $updates['notes'] = $notes;
        }

        $appointment->update($updates);

        return $appointment;
    }

    /**
     * Cancel appointment, freeing the slot in active_slot.
     */
    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        if ($appointment->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Cannot cancel an appointment that has already been completed.',
            ]);
        }

        $notes = $appointment->notes;
        if ($reason) {
            $notes = $notes ? "{$notes}\nCancellation reason: {$reason}" : "Cancellation reason: {$reason}";
        }

        $appointment->update([
            'status' => 'cancelled',
            'notes' => $notes,
        ]);

        return $appointment;
    }

    /**
     * Mark patient as no-show, freeing the slot.
     */
    public function markNoShow(Appointment $appointment): Appointment
    {
        if ($appointment->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Cannot mark a completed appointment as no-show.',
            ]);
        }

        $appointment->update(['status' => 'no_show']);

        return $appointment;
    }
}
