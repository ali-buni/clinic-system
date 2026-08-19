<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $invoiceId = $request->query('invoice_id');

        $payment = $sessionId
            ? Payment::whereBlind('stripe_session_id', 'stripe_session_id_index', $sessionId)->first()
            : null;
        Payment::where('invoice_id', $invoiceId)
            ->update(['paid_at' => now()]);

        $invoice = Invoice::findOrFail($invoiceId);
        $this->syncInvoiceStatus($invoice);

        return view('payment.success', compact('payment'));
    }

    public function failed(Request $request)
    {
        $sessionId = $request->query('session_id');

        $payment = $sessionId
            ? Payment::whereBlind('stripe_session_id', 'stripe_session_id_index', $sessionId)->first()
            : null;

        return view('payment.failed', compact('payment'));
    }

    public function syncInvoiceStatus(Invoice $invoice)
    {
        $invoice->loadMissing(['completedPayments', 'refunds']);

        $totalPaid = $invoice->completedPayments->sum('amount');
        $totalRefunded = $invoice->refunds->sum('amount');
        $netPaid = (float) $totalPaid - (float) $totalRefunded;

        $newStatus = match (true) {
            $netPaid <= 0 && $totalPaid > 0 => InvoiceStatus::Refunded,
            $netPaid >= (float) $invoice->total_cost => InvoiceStatus::Paid,
            $netPaid > 0 => InvoiceStatus::PartiallyPaid,
            default => InvoiceStatus::Draft,
        };

        if ($invoice->status !== $newStatus->value) {
            $invoice->update(['status' => $newStatus->value]);
        }
    }
}
