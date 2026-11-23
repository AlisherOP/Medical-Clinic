<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Patient;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BedAssignmentService
{
    /**
     * Admit a patient into a bed.
     *
     * @throws ValidationException
     */
    public function admit(
        Patient $patient,
        Bed $bed,
        ?Appointment $appointment = null,
        ?Carbon $admittedAt = null
    ): BedAssignment {
        return DB::transaction(function () use ($patient, $bed, $appointment, $admittedAt) {
            // Ensure room is active
            if (! $bed->room->is_active) {
                throw ValidationException::withMessages([
                    'bed_id' => 'Cannot assign a bed in an inactive room.',
                ]);
            }

            // Lock to ensure bed is not occupied
            $occupied = BedAssignment::where('bed_id', $bed->id)
                ->whereNull('discharged_at')
                ->lockForUpdate()
                ->exists();

            if ($occupied) {
                throw ValidationException::withMessages([
                    'bed_id' => 'The selected bed is currently occupied.',
                ]);
            }

            // Ensure patient is not already admitted in another bed
            $alreadyAdmitted = BedAssignment::where('patient_id', $patient->id)
                ->whereNull('discharged_at')
                ->lockForUpdate()
                ->exists();

            if ($alreadyAdmitted) {
                throw ValidationException::withMessages([
                    'patient_id' => 'This patient is already currently admitted to a bed.',
                ]);
            }

            return BedAssignment::create([
                'patient_id' => $patient->id,
                'bed_id' => $bed->id,
                'appointment_id' => $appointment?->id,
                'admitted_at' => $admittedAt ?? now(),
                'discharged_at' => null,
                'status' => 'admitted',
            ]);
        });
    }

    /**
     * Discharge a patient from their bed assignment.
     *
     * @throws ValidationException
     */
    public function discharge(BedAssignment $assignment, ?Carbon $dischargedAt = null): BedAssignment
    {
        if ($assignment->status !== 'admitted' || $assignment->discharged_at !== null) {
            throw ValidationException::withMessages([
                'status' => 'This bed assignment is already closed.',
            ]);
        }

        $time = $dischargedAt ?? now();

        if ($time->lt($assignment->admitted_at)) {
            throw ValidationException::withMessages([
                'discharged_at' => 'Discharge time cannot be prior to admission time.',
            ]);
        }

        $assignment->update([
            'status' => 'discharged',
            'discharged_at' => $time,
        ]);

        return $assignment;
    }

    /**
     * Atomically transfer a patient to a different bed.
     *
     * @throws ValidationException
     */
    public function transfer(BedAssignment $currentAssignment, Bed $newBed): BedAssignment
    {
        return DB::transaction(function () use ($currentAssignment, $newBed) {
            if ($currentAssignment->status !== 'admitted') {
                throw ValidationException::withMessages([
                    'status' => 'Cannot transfer an assignment that is not currently admitted.',
                ]);
            }

            if ($currentAssignment->bed_id === $newBed->id) {
                throw ValidationException::withMessages([
                    'bed_id' => 'Cannot transfer patient to the exact same bed.',
                ]);
            }

            // Ensure new bed is active and unoccupied
            if (! $newBed->room->is_active) {
                throw ValidationException::withMessages([
                    'bed_id' => 'Target room is not currently active.',
                ]);
            }

            $newBedOccupied = BedAssignment::where('bed_id', $newBed->id)
                ->whereNull('discharged_at')
                ->lockForUpdate()
                ->exists();

            if ($newBedOccupied) {
                throw ValidationException::withMessages([
                    'bed_id' => 'The target bed is currently occupied.',
                ]);
            }

            $now = now();

            // Mark old assignment as transferred
            $currentAssignment->update([
                'status' => 'transferred',
                'discharged_at' => $now,
            ]);

            // Create new assignment
            return BedAssignment::create([
                'patient_id' => $currentAssignment->patient_id,
                'bed_id' => $newBed->id,
                'appointment_id' => $currentAssignment->appointment_id,
                'admitted_at' => $now,
                'discharged_at' => null,
                'status' => 'admitted',
            ]);
        });
    }

    /**
     * Check if a bed is occupied.
     */
    public function isBedOccupied(Bed $bed): bool
    {
        return BedAssignment::where('bed_id', $bed->id)
            ->whereNull('discharged_at')
            ->exists();
    }

    /**
     * Get all available (unoccupied) beds in a room.
     *
     * @return Collection<int, Bed>
     */
    public function getAvailableBeds(Room $room): Collection
    {
        return $room->beds()
            ->whereDoesntHave('assignments', function ($query) {
                $query->whereNull('discharged_at');
            })
            ->get();
    }
}
