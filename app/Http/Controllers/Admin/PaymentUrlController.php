<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendPaymentRequest;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentUrlController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Payment::with(['invoice.clinic', 'paymentMethod'])
            ->whereNotNull('stripe_session_id');

        if ($request->filled('clinic_id')) {
            $query->whereHas('invoice', fn ($q) => $q->where('clinic_id', $request->clinic_id));
        }

        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereNotNull('paid_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('paid_at');
            }
        }

        $payments = $query->latest()->paginate(15)->withQueryString();
        $clinics = Clinic::pluck('title', 'id');

        return view('admin.payments.index', compact('payments', 'clinics'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.clinic', 'paymentMethod']);

        return view('admin.payments.show', compact('payment'));
    }

    public function sendPaymentForm(Clinic $clinic)
    {
        $clinic->load('owner');
        $invoices = Invoice::where('clinic_id', $clinic->id)
            ->whereNotIn('status', ['paid', 'void'])
            ->get();

        $stripeMethod = Payment_method::where('type', 'Stripe')->first();

        return view('admin.payments.send-form', compact('clinic', 'invoices', 'stripeMethod'));
    }

    public function sendPayment(SendPaymentRequest $request, Clinic $clinic)
    {
        $stripeMethod = Payment_method::where('type', 'Stripe')->first();

        if (! $stripeMethod) {
            return back()->withErrors(['error' => 'Stripe payment method not configured.']);
        }

        $invoiceId = $request->invoice_id;

        if (! $invoiceId) {
            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'invoice_number' => 'INV-'.strtoupper(uniqid()),
                'status' => 'draft',
                'total_cost' => $request->amount,
                'description' => 'Admin-initiated payment for '.$clinic->title,
            ]);
            $invoiceId = $invoice->id;
        }

        $result = $this->paymentService->processPayment(
            $invoiceId,
            $stripeMethod->id,
            $request->amount
        );

        $payment = Payment::whereBlind('stripe_session_id', 'stripe_session_id_index', $result['session_id'])->first();

        Log::channel('structured')->info('admin sent payment url', [
            'admin_id' => auth()->id(),
            'clinic_id' => $clinic->id,
            'invoice_id' => $invoiceId,
            'amount' => $request->amount,
            'payment_id' => $payment?->id,
        ]);

        if ($clinic->owner && $clinic->owner->email) {
            try {
                Mail::raw("Payment URL for clinic {$clinic->title}: {$result['payment_url']}", function ($message) use ($clinic) {
                    $message->to($clinic->owner->email)
                        ->subject('Payment URL - Clinic System');
                });
            } catch (\Exception $e) {
                Log::channel('structured')->warning('failed to send payment email', [
                    'clinic_id' => $clinic->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('admin.payments.success', [
            'payment' => $payment,
            'paymentUrl' => $result['payment_url'],
            'clinic' => $clinic,
        ]);
    }

    public function resend(Payment $payment)
    {
        $payment->load('invoice.clinic.owner');

        if (! $payment->stripe_session_id) {
            return back()->withErrors(['error' => 'No Stripe session found for this payment.']);
        }

        $clinic = $payment->invoice->clinic;

        if ($clinic && $clinic->owner && $clinic->owner->email) {
            try {
                $paymentUrl = "https://checkout.stripe.com/pay/{$payment->stripe_session_id}";
                Mail::raw("Payment URL for invoice #{$payment->invoice->invoice_number}: {$paymentUrl}", function ($message) use ($clinic) {
                    $message->to($clinic->owner->email)
                        ->subject('Payment URL - Clinic System (Resent)');
                });

                return back()->with('success', 'Payment URL resent successfully.');
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Failed to resend email: '.$e->getMessage()]);
            }
        }

        return back()->withErrors(['error' => 'Clinic owner not found.']);
    }
}
