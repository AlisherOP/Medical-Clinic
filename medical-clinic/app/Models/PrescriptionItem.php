<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration_days',
        'quantity',
        'instructions',
        'dispensed_at',
        'dispensed_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'quantity' => 'integer',
            'dispensed_at' => 'datetime',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
