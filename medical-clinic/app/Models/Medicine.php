<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'category',
        'stock_quantity',
        'unit',
        'unit_price',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
