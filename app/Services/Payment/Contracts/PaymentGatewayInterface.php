<?php

namespace App\Services\Payment\Contracts;

use App\Enums\PaymentMethodType;
use App\Models\Invoice;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createPayment(Invoice $invoice, Payment $payment, float $amount): array;

    public function confirmPayment(Payment $payment): void;

    public function cancelPayment(Payment $payment): void;

    public function refundPayment(Payment $payment, float $amount, ?string $idempotencyKey = null): array;

    public function supports(PaymentMethodType $type): bool;
}
