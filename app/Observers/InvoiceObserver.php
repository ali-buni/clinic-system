<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\ActivityLogService;

class InvoiceObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Invoice $invoice): void
    {
        $this->activityLog->log(
            'invoice',
            'created invoice',
            $invoice,
            auth()->user(),
            [
                'invoice_number' => $invoice->invoice_number,
                'total_cost' => $invoice->total_cost,
                'clinic_id' => $invoice->clinic_id,
                'patient_id' => $invoice->patient_id,
            ],
            'created'
        );
    }

    public function updated(Invoice $invoice): void
    {
        $oldStatus = $invoice->getOriginal('status');
        $newStatus = $invoice->status;
        $oldTotalCost = $invoice->getOriginal('total_cost');
        $newTotalCost = $invoice->total_cost;

        $details = [];
        if ($oldStatus !== $newStatus) {
            $details['status_transition'] = ['from' => $oldStatus, 'to' => $newStatus];
        }
        if ($oldTotalCost !== $newTotalCost) {
            $details['total_cost_changed'] = ['from' => $oldTotalCost, 'to' => $newTotalCost];
        }
        if ($invoice->wasChanged('description')) {
            $details['description_changed'] = true;
        }

        $this->activityLog->log(
            'invoice',
            'updated invoice',
            $invoice,
            auth()->user(),
            $details,
            'updated'
        );
    }

    public function deleted(Invoice $invoice): void
    {
        $this->activityLog->log(
            'invoice',
            'deleted invoice',
            $invoice,
            auth()->user(),
            [],
            'deleted'
        );
    }

    public function restored(Invoice $invoice): void
    {
        $this->activityLog->log(
            'invoice',
            'restored invoice',
            $invoice,
            auth()->user(),
            [],
            'restored'
        );
    }
}
