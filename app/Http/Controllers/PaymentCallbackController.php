<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentCallbackController extends Controller
{
    public function success(Request $request)
    {
        try {
            $sessionId = $request->query('session_id');
            $invoiceId = $request->query('invoice_id');

            if (! $invoiceId) {
                return view('payment.failed', ['error' => 'Invoice ID is missing in the callback.']);
            }

            $payment = $sessionId
                ? Payment::whereBlind('stripe_session_id', 'stripe_session_id_index', $sessionId)
                ->where('invoice_id', $invoiceId)
                ->first()
                : null;

            if (! $payment) {
                return view('payment.failed', ['error' => 'Payment not found for the provided session ID and invoice ID.']);
            }

            $payment->update(['paid_at' => now()]);

            $invoice = Invoice::find($invoiceId);

            $this->syncInvoiceStatus($invoice);
            return view('payment.success', compact('payment', 'invoice'));
        } catch (\Exception $e) {
            logger('Payment success callback error: ' . $e->getMessage());
            return view('payment.failed');
        }
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
