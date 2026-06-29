<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Exceptions\PaymentExceedsBalanceException;
use Exception;
use Illuminate\Support\Facades\DB;


class PaymentService
{
    /**
     * تجهيز الدفعة المالية وتوليد جلسة Stripe Checkout
     */



    public function createStripeSession(int $invoiceId, int $paymentMethodId, float $amount): array
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $remainingAmount = $invoice->getRemainingBalance();

        if ($invoice->status === 'paid') {
            throw new \Exception('Invoice_already_paid');
        }

        if ($remainingAmount < $amount) {
            throw new PaymentExceedsBalanceException($remainingAmount);
        }
        // 1. تسجيل حركة الدفع بجدول الـ payments كـ pending
        $payment = Payment::create([
            'invoice_id'        => $invoice->id,
            'payment_method_id' => $paymentMethodId,
            'amount'            => $amount,
            'paid_at'           => null,
        ]);

        // 2. إعداد جلسة Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'دفعة مالية للفاتورة رقم: ' . $invoice->invoice_number,
                    ],
                    'unit_amount' => $amount * 100, // تحويل لسنتات
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/payment-success?invoice_id=' . $invoice->id,
            'cancel_url' => 'http://localhost:3000/payment-failed',
            'metadata' => [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id
            ]
        ]);

        return [
            'payment_url' => $session->url,
            'payment_id'  => $payment->id
        ];
    }

    /**
     * تأكيد الدفعة وتحديث حالة الفاتورة وحساب المتبقي (عند نجاح الـ Webhook)
     */
    public function confirmPayment(int $invoiceId, int $paymentId): void
    {
        // 1. تثبيت تاريخ الدفع الفعلي للعملية
        $payment = Payment::find($paymentId);
        if ($payment && is_null($payment->paid_at)) {
            $payment->update([
                'paid_at' => now()
            ]);
        }

        // 2. تحديث الفاتورة وإعادة حساب المبالغ المتبقية
        $invoice = Invoice::find($invoiceId);
        if ($invoice) {
            $totalCost = $invoice->total_cost; // إجمالي الفاتورة المطلوب[cite: 1]

            // جمع كل المدفوعات المكتملة المتصلة بالفاتورة
            $totalPaidSoFar = Payment::where('invoice_id', $invoiceId)
                ->whereNotNull('paid_at')
                ->sum('amount');

            $remainingAmount = $totalCost - $totalPaidSoFar;

            // 3. تحديث الحالة آلياً بناءً على النتيجة المالية[cite: 1]
            if ($remainingAmount <= 0) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partially_paid']);
            }
        }
    }

    public function cancelPayment(int $payment_id)
    {
        // البدء في المعاملة لضمان سلامة البيانات
        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($payment_id);
            $invoice = Invoice::findOrFail($payment->invoice_id);

            if ($payment->paid_at === null) {
                throw new Exception('Payment_is_not_completed_yet');
            }

            // ملاحظة: إذا كنت تستخدم SoftDeletes، الـ findOrFail لن يجد المحذوف أصلاً إلا إذا استخدمت withTrashed
            if ($payment->deleted_at !== null) {
                throw new Exception('Payment_already_canceled');
            }

            // --- التعامل مع بوابة الدفع Stripe ---
            // افترضت هنا أن اسم الحقل عندك payment_method، عدله بحسب تسميتك بالداتابيز (مثلاً: stripe, cash)
            if ($payment->payment_method === 'stripe') {

                // تأكد من أنك مخزن الـ Charge ID أو PaymentIntent ID الخاص بسترايب عند الدفع
                if (!$payment->stripe_charge_id) {
                    throw new Exception('Stripe_charge_id_missing');
                }

                // استدعاء مكتبة سترايب وعمل الـ Refund
                $stripe = new StripeClient(config('services.stripe.secret'));

                $stripe->refunds->create([
                    'payment_intent' => $payment->stripe_charge_id, // أو 'charge' بحسب شو مخزن عندك
                ]);
            }

            // حذف الدفعة (سواء كاش أو سترايب بعد نجاح الـ Refund)
            $payment->delete();

            // تحديث حالة الفاتورة بناءً على المبلغ المتبقي
            $remainingAmount = $invoice->getRemainingBalance();

            if ($remainingAmount == $invoice->total_cost) {
                $invoice->update(['status' => 'draft']);
            } elseif ($remainingAmount > 0) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'paid']);
            }

            // تثبيت العمليات في حال نجاح كل شيء
            DB::commit();
        } catch (Exception $e) {
            // التراجع عن أي تغيير في الداتابيز لو فشل الـ Refund أو أي خطأ آخر
            DB::rollBack();

            // إعادة رمي الخطأ ليظهر بالـ API أو الـ Controller
            throw $e;
        }
    }
    public function processCashPayment(int $invoiceId, float $amount): Invoice
    {
        // استخدام Transactions لضمان حفظ كل شيء أو تراجعه في حال حدوث خطأ
        return DB::transaction(function () use ($invoiceId, $amount) {

            // 1. جلب الفاتورة وقفل السطر في قاعدة البيانات لمنع التضارب (Race Conditions)
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            // 2. حساب الرصيد المتبقي الحالي بالفاتورة
            $remainingBalance = $invoice->getRemainingBalance();

            // 3. التحقق من أن مبلغ الكاش لا يتجاوز المتبقي
            if ($amount > $remainingBalance) {
                throw new PaymentExceedsBalanceException(
                    $remainingBalance,
                    "المبلغ المدفوع يتجاوز الرصيد المتبقي للفاتورة."
                );
            }

            // 4. تسجيل عملية الدفع فوراً كـ "مدفوعة كاش" وتاريخها الآن
            $invoice->payments()->create([
                'payment_method_id' => 1, // 1 يعبر عن الكاش
                'amount' => $amount,
                'paid_at' => now(), // مدفوعة فوراً
                'status' => 'completed', // أو الحالة المعتمدة عندك للدفع الناجح
                'transaction_id' => 'CASH-' . strtoupper(uniqid()), // توليد رقم حركة داخلي للكاش
            ]);

            // 5. تحديث الرصيد المتبقي وحالة الفاتورة بناءً على الحسبة الجديدة
            $newRemaining = $remainingBalance - $amount;

            if ($newRemaining == 0) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }

            $invoice->save();

            return $invoice;
        });
    }
}
