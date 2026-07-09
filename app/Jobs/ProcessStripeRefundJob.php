<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Refund;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStripeRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public readonly int $paymentId,
        public readonly float $amount,
        public readonly ?string $reason = null,
        public readonly ?int $refundedBy = null,
    ) {
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        $payment = Payment::with('paymentMethod')->findOrFail($this->paymentId);

        if (! $payment->stripe_payment_intent_id || $this->amount <= 0) {
            return;
        }

        $gateway = new StripeGateway;
        $result = $gateway->refundPayment($payment, $this->amount);

        $refund = Refund::create([
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'refunded_by' => $this->refundedBy,
            'stripe_refund_id' => $result['stripe_refund_id'] ?? null,
        ]);
        
        $payment->refunded_amount = (float) $payment->refunded_amount + $this->amount;
        $payment->save();

        Log::channel('structured')->info('Stripe refund processed', [
            'payment_id' => $payment->id,
            'refund_id' => $refund->id,
            'amount' => $this->amount,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('ProcessStripeRefundJob failed', [
            'payment_id' => $this->paymentId,
            'amount' => $this->amount,
            'error' => $exception->getMessage(),
        ]);
    }
}
