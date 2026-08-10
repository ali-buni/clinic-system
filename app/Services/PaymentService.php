<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Exceptions\PaymentExceedsBalanceException;
use App\Jobs\ProcessStripeRefundJob;
use App\Jobs\SyncDoctorWalletJob;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Refund;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\CashGateway;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct() {}

    public function processPayment(int $invoiceId, int $paymentMethodId, float $amount): array
    {
        return DB::transaction(function () use ($invoiceId, $paymentMethodId, $amount) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            if (
                $invoice->status === InvoiceStatus::Paid->value ||
                $invoice->status === InvoiceStatus::Refunded->value ||
                $invoice->status === InvoiceStatus::Void->value
            ) {
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

            if ($method->type === PaymentMethodType::Cash && ! auth()->user()?->hasAnyRole(['secretary', 'owner'])) {
                throw new \RuntimeException('Cash payments can only be recorded by staff.');
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

            if (! $payment || $payment->paid_at !== null) {
                return;
            }

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);
            $confirmed = $gateway->confirmPayment($payment);

            if (! $confirmed) {
                throw new \RuntimeException('payment confirmation failed');
            }

            $payment->update(['paid_at' => now()]);

            $invoice = Invoice::lockForUpdate()->find($invoiceId);

            if ($invoice) {
                $this->syncInvoiceStatus($invoice);
                $this->dispatchWalletSync($invoice);
            }
        });
    }

    public function refundPayment(int $paymentId, float $amount, ?string $reason = null, ?int $refundedBy = null): Refund
    {
        $refund = DB::transaction(function () use ($paymentId, $amount, $reason, $refundedBy) {
            $payment = Payment::lockForUpdate()->findOrFail($paymentId);

            if ($amount <= 0) {
                throw new \RuntimeException('refund amount must be greater than zero');
            }

            $refundable = $payment->getRefundableAmount();
            if ($amount > $refundable) {
                throw new \RuntimeException("refund amount exceeds refundable balance of {$refundable}");
            }

            $idempotencyKey = StripeGateway::refundIdempotencyKey($payment->id, $amount);

            $existing = Refund::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $method = Payment_method::findOrFail($payment->payment_method_id);
            $gateway = $this->resolveGateway($method);

            $refund = Refund::create([
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $amount,
                'reason' => $reason,
                'refunded_by' => $refundedBy,
                'stripe_refund_id' => null,
                'idempotency_key' => $idempotencyKey,
            ]);

            $result = $gateway->refundPayment($payment, $amount, $idempotencyKey);

            if (($result['stripe_refund_id'] ?? null) !== null) {
                $refund->update(['stripe_refund_id' => $result['stripe_refund_id']]);
            }

            $payment->refunded_amount = (float) $payment->refunded_amount + $amount;
            $payment->save();

            return $refund;
        });

        $invoice = Invoice::lockForUpdate()->find($refund->invoice_id);
        if ($invoice) {
            $this->syncInvoiceStatus($invoice);
            $this->dispatchWalletSync($invoice);
        }

        return $refund;
    }

    public function refundOverpaidStripePayments(Invoice $invoice, float $newTotalCost): float
    {
        $invoice->loadMissing(['completedPayments.paymentMethod']);

        $cashPaid = $invoice->completedPayments
            ->filter(fn ($p) => $p->paymentMethod->type === PaymentMethodType::Cash)
            ->sum('amount');

        $stripePayments = $invoice->completedPayments
            ->filter(fn ($p) => in_array($p->paymentMethod->type, [PaymentMethodType::Stripe, PaymentMethodType::Card]) && $p->stripe_payment_intent_id)
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

            ProcessStripeRefundJob::dispatchSync($payment->id, $refundable, 'Auto-refund: invoice total decreased');

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
            $this->dispatchWalletSync($invoice);
        });
    }

    public function syncInvoiceStatus(Invoice $invoice): void
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

    private function resolveGateway(Payment_method $method): PaymentGatewayInterface
    {
        return match ($method->type) {
            PaymentMethodType::Cash => new CashGateway,
            PaymentMethodType::Card => new StripeGateway,
            PaymentMethodType::Stripe => new StripeGateway,
            PaymentMethodType::BankTransfer => new CashGateway,
            default => throw new \InvalidArgumentException("No gateway for type: {$method->type->value}"),
        };
    }

    private function dispatchWalletSync(Invoice $invoice): void
    {
        if (! $invoice->appointment || ! $invoice->appointment->doctor_id) {
            return;
        }

        SyncDoctorWalletJob::dispatch($invoice->appointment->doctor_id);
    }
}
