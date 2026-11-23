<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabOrderService
{
    /**
     * Create a new lab order for a patient.
     */
    public function order(Patient $patient, LabTest $test, ?Appointment $appointment = null): LabOrder
    {
        return LabOrder::create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment?->id,
            'lab_test_id' => $test->id,
            'status' => 'requested',
            'ordered_at' => now(),
        ]);
    }

    /**
     * Complete a lab order, storing the optional confidential attachment securely in private storage.
     */
    public function complete(
        LabOrder $order,
        User $performedBy,
        string $resultSummary,
        ?UploadedFile $attachment = null
    ): LabOrder {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Cannot complete an order that has been cancelled.',
            ]);
        }

        $attachmentPath = $order->attachment_url;

        if ($attachment) {
            // Validate MIME type for security
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (! in_array($attachment->getMimeType(), $allowedMimes, true)) {
                throw ValidationException::withMessages([
                    'attachment' => 'Attachment must be a PDF document or a JPEG/PNG image.',
                ]);
            }

            // Store in private storage directory
            $attachmentPath = $attachment->store('private/lab_results', 'local');
        }

        $order->update([
            'performed_by' => $performedBy->id,
            'result_summary' => $resultSummary,
            'attachment_url' => $attachmentPath,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $order;
    }

    /**
     * Cancel a lab order.
     */
    public function cancel(LabOrder $order, ?string $reason = null): LabOrder
    {
        if ($order->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Cannot cancel an order that has already been completed.',
            ]);
        }

        $summary = $order->result_summary;
        if ($reason) {
            $summary = $summary ? "{$summary}\nCancelled: {$reason}" : "Cancelled: {$reason}";
        }

        $order->update([
            'status' => 'cancelled',
            'result_summary' => $summary,
        ]);

        return $order;
    }

    /**
     * Download or stream the confidential attachment.
     */
    public function downloadAttachment(LabOrder $order): StreamedResponse
    {
        if (! $order->attachment_url || ! Storage::disk('local')->exists($order->attachment_url)) {
            abort(404, 'Lab result file not found.');
        }

        return Storage::disk('local')->download($order->attachment_url);
    }
}
