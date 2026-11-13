<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    // user_id is not fillable: a profile is attached to its user via the relation
    // ($user->patient()->create([...])) and must never be re-pointed by form input.
    protected $fillable = [
        'mrn',
        'blood_group',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'insurance_provider',
        'insurance_policy_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function bedAssignments(): HasMany
    {
        return $this->hasMany(BedAssignment::class);
    }
}
