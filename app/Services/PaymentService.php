<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Exceptions\PaymentExceedsBalanceException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Refund;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment(int $invoiceId, int $paymentMethodId, float $amount): array
    {
        return DB::transaction(function () use ($invoiceId, $paymentMethodId, $amount) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            if ($invoice->status === InvoiceStatus::Paid->value) {
                throw new \RuntimeException('the invoice is paid');
            }

            $remainingBalance = $invoice->getRemainingBalance();

            if ($amount > $remainingBalance) {
                throw new PaymentExceedsBalanceException($remainingBalance);
            }

            $method = Payment_method::findOrFail($paymentMethodId);
            if ($method->is_active !== true) {
                throw new ModelNotFoundException('the payment method not found');
            }
            $payment = $invoice->payments()->create([
                'payment_method_id' => $paymentMethodId,
                'amount' => $amount,
                'paid_at' => $method->type === PaymentMethodType::Cash ? now() : null,
            ]);
            $this->syncInvoiceStatus($invoice);

            $gateway = $this->resolveGateway($method);
            $result = $gateway->createPayment($invoice, $payment, $amount);

            return $result;
        });
    }

    public function confirmPayment(int $invoiceId, int $paymentId): void
    {
        DB::transaction(function () use ($invoiceId, $paymentId) {
            $payment = Payment::lockForUpdate()->find($paymentId);

            if (!$payment || $payment->paid_at !== null) {
                return;
            }

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);
            $gateway->confirmPayment($payment);

            $payment->update(['paid_at' => now()]);

            $invoice = Invoice::lockForUpdate()->find($invoiceId);

            if ($invoice) {
                $this->syncInvoiceStatus($invoice);
            }
        });
    }

    public function refundPayment(int $paymentId, float $amount, ?string $reason = null, ?int $refundedBy = null): Refund
    {
        return DB::transaction(function () use ($paymentId, $amount, $reason, $refundedBy) {
            $payment = Payment::lockForUpdate()->findOrFail($paymentId);

            if ($amount <= 0) {
                throw new \RuntimeException('refund amount must be greater than zero');
            }

            $refundable = $payment->getRefundableAmount();
            if ($amount > $refundable) {
                throw new \RuntimeException("refund amount exceeds refundable balance of {$refundable}");
            }

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);
            $result = $gateway->refundPayment($payment, $amount);

            $refund = Refund::create([
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $amount,
                'reason' => $reason,
                'refunded_by' => $refundedBy,
                'stripe_refund_id' => $result['stripe_refund_id'] ?? null,
            ]);

            $payment->increment('refunded_amount', $amount);

            $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);
            if ($invoice) {
                $this->syncInvoiceStatus($invoice);
            }

            return $refund;
        });
    }

    public function refundOverpaidStripePayments(Invoice $invoice, float $newTotalCost): float
    {
        $invoice->loadMissing(['completedPayments.paymentMethod']);

        $cashPaid = $invoice->completedPayments
            ->filter(fn($p) => $p->paymentMethod->type === PaymentMethodType::Cash)
            ->sum('amount');

        $stripePayments = $invoice->completedPayments
            ->filter(fn($p) => in_array($p->paymentMethod->type, [PaymentMethodType::Stripe, PaymentMethodType::Card]) && $p->stripe_payment_intent_id)
            ->sortByDesc('paid_at');

        $stripePaid = $stripePayments->sum('amount');
        $stripeCoverAmount = max(0, $newTotalCost - $cashPaid);
        $refundAmount = max(0, $stripePaid - $stripeCoverAmount);

        if ($refundAmount <= 0) {
            return 0;
        }

        $remainingRefund = $refundAmount;

        foreach ($stripePayments as $payment) {
            if ($remainingRefund <= 0) {
                break;
            }

            $refundable = min($payment->getRefundableAmount(), $remainingRefund);
            if ($refundable <= 0) {
                continue;
            }

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);
            $result = $gateway->refundPayment($payment, $refundable);

            Refund::create([
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $refundable,
                'reason' => 'Auto-refund: invoice total decreased',
                'stripe_refund_id' => $result['stripe_refund_id'] ?? null,
            ]);

            $payment->increment('refunded_amount', $refundable);
            $remainingRefund -= $refundable;
        }

        $invoice->refresh();
        $this->syncInvoiceStatus($invoice);

        return $refundAmount - $remainingRefund;
    }

    public function cancelPayment(int $paymentId): void
    {
        DB::transaction(function () use ($paymentId) {
            $payment = Payment::lockForUpdate()->findOrFail($paymentId);
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);

            throw_if(
                $payment->paid_at === null,
                new \RuntimeException('no payment paid')
            );

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);
            $gateway->cancelPayment($payment);

            $payment->delete();
            $this->syncInvoiceStatus($invoice);
        });
    }

    public function syncInvoiceStatus(Invoice $invoice): void
    {
        $totalPaid = $invoice->completedPayments()->sum('amount');
        $totalRefunded = $invoice->refunds()->sum('amount');
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

    private function resolveGateway(Payment_method $method): PaymentGatewayInterface
    {
        return match ($method->type) {
            PaymentMethodType::Cash         => new \App\Services\Payment\Gateways\CashGateway(),
            PaymentMethodType::Card         => new \App\Services\Payment\Gateways\StripeGateway(),
            PaymentMethodType::Stripe       => new \App\Services\Payment\Gateways\StripeGateway(),
            PaymentMethodType::BankTransfer => new \App\Services\Payment\Gateways\CashGateway(),
            default => throw new \InvalidArgumentException("No gateway for type: {$method->type->value}"),
        };
    }
}
