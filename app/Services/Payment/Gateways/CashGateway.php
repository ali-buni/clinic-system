<?php

namespace App\Services\Payment\Gateways;

use App\Enums\PaymentMethodType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentGatewayInterface;

class CashGateway implements PaymentGatewayInterface
{
    public function supports(PaymentMethodType $type): bool
    {
        return $type === PaymentMethodType::Cash;
    }

    public function createPayment(Invoice $invoice, Payment $payment, float $amount, ?string $idempotencyKey = null): array
    {
        $payment->update(['paid_at' => now()]);

        return [
            'invoice_id' => $invoice->id,
            'status' => $invoice->fresh()->status,
            'remaining_balance' => $invoice->fresh()->getRemainingBalance(),
        ];
    }

    public function confirmPayment(Payment $payment): bool
    {
        return true;
    }

    public function cancelPayment(Payment $payment): void {}

    public function refundPayment(Payment $payment, float $amount, ?string $idempotencyKey = null): array
    {
        return ['stripe_refund_id' => null];
    }
}
