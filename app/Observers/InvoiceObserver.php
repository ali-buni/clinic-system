<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

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
            [],
            'created'
        );
        Log::channel('structured')->info('invoice created', ['invoice_id' => $invoice->id]);
    }

    public function updated(Invoice $invoice): void
    {
        $this->activityLog->log(
            'invoice',
            'updated invoice',
            $invoice,
            auth()->user(),
            [],
            'updated'
        );
        Log::channel('structured')->info('invoice updated', ['invoice_id' => $invoice->id]);
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
        Log::channel('structured')->info('invoice deleted', ['invoice_id' => $invoice->id]);
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
        Log::channel('structured')->info('invoice restored', ['invoice_id' => $invoice->id]);
    }
}
