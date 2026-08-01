<?php

namespace App\Services\Payment\Gateways;

use App\Enums\PaymentMethodType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeGateway implements PaymentGatewayInterface
{
    private StripeClient $client;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    public function supports(PaymentMethodType $type): bool
    {
        return $type === PaymentMethodType::Stripe || $type === PaymentMethodType::Card;
    }

    public function createPayment(Invoice $invoice, Payment $payment, float $amount): array
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
                    'product_data' => [
                        'name' => $invoice->invoice_number,
                    ],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('services.stripe.success_url').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('services.stripe.cancel_url'),
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
                'payment_id' => (string) $payment->id,
            ],
        ]);

        $payment->update([
            'stripe_session_id' => $session->id,
        ]);

        return [
            'payment_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    public function confirmPayment(Payment $payment): void
    {
        if (! $payment->stripe_session_id) {
            return;
        }

        try {
            $session = Session::retrieve($payment->stripe_session_id);

            $payment->update([
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);
        } catch (ApiErrorException $e) {
            Log::channel('structured')->warning('Failed to retrieve Stripe session for payment', [
                'payment_id' => $payment->id,
                'session_id' => $payment->stripe_session_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cancelPayment(Payment $payment): void
    {
        if (! $payment->stripe_payment_intent_id) {
            return;
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);

            if ($paymentIntent->status !== PaymentIntent::STATUS_SUCCEEDED) {
                Log::channel('structured')->warning('Cannot refund unpaid payment intent', [
                    'payment_id' => $payment->id,
                    'payment_intent' => $payment->stripe_payment_intent_id,
                    'status' => $paymentIntent->status,
                ]);

                return;
            }

            $this->client->refunds->create([
                'payment_intent' => $payment->stripe_payment_intent_id,
            ]);
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Stripe refund failed (cancelPayment)', [
                'payment_id' => $payment->id,
                'payment_intent' => $payment->stripe_payment_intent_id,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('the refund is failed');
        }
    }

    public static function refundIdempotencyKey(int $paymentId, float $amount): string
    {
        return sprintf('refund-%d-%d', $paymentId, (int) round($amount * 100));
    }

    public function refundPayment(Payment $payment, float $amount, ?string $idempotencyKey = null): array
    {
        if (! $payment->stripe_payment_intent_id || $amount <= 0) {
            return ['stripe_refund_id' => null];
        }

        try {
            $params = [
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => (int) round($amount * 100),
            ];

            if ($idempotencyKey !== null) {
                $params['idempotency_key'] = $idempotencyKey;
            }

            $refund = $this->client->refunds->create($params);

            return ['stripe_refund_id' => $refund->id];
        } catch (ApiErrorException $e) {
            Log::channel('structured')->error('Stripe refund failed (refundPayment)', [
                'payment_id' => $payment->id,
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('the refund is failed');
        }
    }
}
