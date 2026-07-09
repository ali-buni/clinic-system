<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Invoice;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        LogActivityJob::dispatch(
            'invoice',
            'created invoice',
            get_class($invoice),
            $invoice->id,
            auth()->id(),
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

        LogActivityJob::dispatch(
            'invoice',
            'updated invoice',
            get_class($invoice),
            $invoice->id,
            auth()->id(),
            $details,
            'updated'
        );
    }

    public function deleted(Invoice $invoice): void
    {
        LogActivityJob::dispatch(
            'invoice',
            'deleted invoice',
            get_class($invoice),
            $invoice->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(Invoice $invoice): void
    {
        LogActivityJob::dispatch(
            'invoice',
            'restored invoice',
            get_class($invoice),
            $invoice->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
