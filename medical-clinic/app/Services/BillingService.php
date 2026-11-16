<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Create an invoice with optional initial line items.
     *
     * @param  array<int, array{
     *     description: string,
     *     item_type: string,
     *     unit_price: float,
     *     quantity?: int,
     *     billable?: ?Model
     * }>  $items
     *
     * @throws ValidationException
     */
    public function createInvoice(
        Patient $patient,
        ?Appointment $appointment = null,
        array $items = [],
        float $discountAmount = 0.00
    ): Invoice {
        return DB::transaction(function () use ($patient, $appointment, $items, $discountAmount) {
            $total = 0.00;
            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $unit = (float) $item['unit_price'];
                $total += round($qty * $unit, 2);
            }

            if ($discountAmount < 0.00 || $discountAmount > $total) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount amount must be non-negative and cannot exceed total invoice amount.',
                ]);
            }

            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'appointment_id' => $appointment?->id,
                'total_amount' => $total,
                'discount_amount' => $discountAmount,
                'paid_amount' => 0.00,
                'status' => 'unpaid',
            ]);

            foreach ($items as $item) {
                $billable = $item['billable'] ?? null;
                $invoice->items()->create([
                    'description' => $item['description'],
                    'item_type' => $item['item_type'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'] ?? 1,
                    'billable_type' => $billable ? get_class($billable) : null,
                    'billable_id' => $billable?->getKey(),
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Add a line item to an existing unpaid or partially paid invoice.
     *
     * @throws ValidationException
     */
    public function addItem(
        Invoice $invoice,
        string $description,
        string $itemType,
        float $unitPrice,
        int $quantity = 1,
        ?Model $billable = null
    ): InvoiceItem {
        return DB::transaction(function () use ($invoice, $description, $itemType, $unitPrice, $quantity, $billable) {
            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be greater than zero.',
                ]);
            }

            if ($unitPrice < 0.00) {
                throw ValidationException::withMessages([
                    'unit_price' => 'Unit price must be non-negative.',
                ]);
            }

            $item = $invoice->items()->create([
                'description' => $description,
                'item_type' => $itemType,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'billable_type' => $billable ? get_class($billable) : null,
                'billable_id' => $billable?->getKey(),
            ]);

            $newTotal = round((float) $invoice->total_amount + ($quantity * $unitPrice), 2);
            $invoice->update(['total_amount' => $newTotal]);

            return $item;
        });
    }

    /**
     * Record a payment and synchronize invoice balance and status.
     *
     * @throws ValidationException
     */
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $paymentMethod,
        User $receivedBy,
        ?string $transactionReference = null
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $paymentMethod, $receivedBy, $transactionReference) {
            // Re-fetch and lock invoice row
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($amount <= 0.00) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            $netPayable = round((float) $lockedInvoice->total_amount - (float) $lockedInvoice->discount_amount, 2);
            $currentPaid = (float) $lockedInvoice->paid_amount;
            $remaining = round($netPayable - $currentPaid, 2);

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount ({$amount}) exceeds the remaining balance ({$remaining}).",
                ]);
            }

            $payment = Payment::create([
                'invoice_id' => $lockedInvoice->id,
                'received_by' => $receivedBy->id,
                'type' => 'payment',
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $transactionReference,
            ]);

            $newPaid = round($currentPaid + $amount, 2);
            $newStatus = ($newPaid >= $netPayable) ? 'paid' : 'partially_paid';

            $lockedInvoice->update([
                'paid_amount' => $newPaid,
                'status' => $newStatus,
            ]);

            return $payment;
        });
    }

    /**
     * Apply or update a discount on an invoice.
     *
     * @throws ValidationException
     */
    public function applyDiscount(Invoice $invoice, float $discountAmount): Invoice
    {
        return DB::transaction(function () use ($invoice, $discountAmount) {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $total = (float) $lockedInvoice->total_amount;
            $paid = (float) $lockedInvoice->paid_amount;

            if ($discountAmount < 0.00 || $discountAmount > $total) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount amount cannot be negative or exceed total amount.',
                ]);
            }

            if ($paid > ($total - $discountAmount)) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot reduce total payable below the amount already paid.',
                ]);
            }

            $lockedInvoice->update(['discount_amount' => $discountAmount]);

            // Re-evaluate status if now fully paid
            $net = round($total - $discountAmount, 2);
            if ($paid >= $net && $net > 0.00) {
                $lockedInvoice->update(['status' => 'paid']);
            }

            return $lockedInvoice;
        });
    }
}
