<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($this->isDuplicateEvent($event->id)) {
            return response()->json(['status' => 'duplicate_ignored'], 200);
        }

        $this->storeEventId($event->id, $event->type);

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
                'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event->data->object),
                'charge.refunded' => $this->handleChargeRefunded($event->data->object),
                default => Log::info('Unhandled Stripe event', ['type' => $event->type]),
            };
        } catch (\Throwable $e) {
            Log::error('Webhook processing failed', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (($session->payment_status ?? '') !== 'paid') {
            Log::info('Checkout session not paid, skipping', [
                'session_id' => $session->id,
                'payment_status' => $session->payment_status ?? 'unknown',
            ]);
            return;
        }

        $invoiceId = $session->metadata->invoice_id ?? null;
        $paymentId = $session->metadata->payment_id ?? null;

        if (!$invoiceId || !$paymentId) {
            Log::warning('Missing metadata in checkout.session.completed', ['session_id' => $session->id]);
            return;
        }

        $invoice = Invoice::find($invoiceId);
        $payment = Payment::find($paymentId);

        if ($invoice && $payment) {
            $this->paymentService->confirmPayment($invoice->id, $payment->id);
        }
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);
    }

    private function handleChargeRefunded(object $charge): void
    {
        Log::info('Charge refunded', ['id' => $charge->id]);
    }

    private function isDuplicateEvent(string $eventId): bool
    {
        return DB::table('webhook_events')->where('event_id', $eventId)->exists();
    }

    private function storeEventId(string $eventId, string $type): void
    {
        DB::table('webhook_events')->insertOrIgnore([
            'event_id' => $eventId,
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
