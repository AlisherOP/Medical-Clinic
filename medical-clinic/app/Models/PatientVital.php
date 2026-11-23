<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVital extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'bed_assignment_id',
        'recorded_by',
        'systolic_bp',
        'diastolic_bp',
        'heart_rate',
        'temperature',
        'weight_kg',
        'height_cm',
    ];

    protected function casts(): array
    {
        return [
            'systolic_bp' => 'integer',
            'diastolic_bp' => 'integer',
            'heart_rate' => 'integer',
            'temperature' => 'decimal:1',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function bedAssignment(): BelongsTo
    {
        return $this->belongsTo(BedAssignment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
