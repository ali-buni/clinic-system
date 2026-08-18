<?php

namespace App\Http\Controllers;

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
}
