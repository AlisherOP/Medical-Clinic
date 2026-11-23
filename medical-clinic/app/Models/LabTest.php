<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_name',
        'code',
        'category',
        'price',
        'turnaround_time_hours',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'turnaround_time_hours' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }
}
