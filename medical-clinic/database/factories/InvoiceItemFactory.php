<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'description' => 'Consultation fee',
            'item_type' => 'consultation',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'billable_type' => null,
            'billable_id' => null,
        ];
    }
}
