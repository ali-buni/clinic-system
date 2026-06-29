<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\PaymentService;
use App\Services\ApiResponse;
use App\Exceptions\PaymentExceedsBalanceException;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id'        => 'required|integer|exists:invoices,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'amount'            => 'required|numeric|min:0.01',
        ]);

        try {
            // 1. إذا كانت طريقة الدفع كاش (ID = 1)
            if ($request->payment_method_id == 1) {

                // استدعاء ميثod الكاش الجديدة وتمرير المعطيات لها
                $invoice = $this->paymentService->processCashPayment(
                    $request->invoice_id,
                    $request->amount
                );

                return ApiResponse::success([
                    'invoice_id' => $invoice->id,
                    'status'     => $invoice->status, // ليرجع الحالة الجديدة (مدفوعة أو مدفوعة جزئياً)
                    'remaining_balance' => number_format($invoice->getRemainingBalance(),2,'.','')
                ], 'تم تسجيل الدفع النقدي وتحديث الفاتورة بنجاح.');
            }

            $result = $this->paymentService->createStripeSession(
                $request->invoice_id,
                $request->payment_method_id,
                $request->amount
            );
            return ApiResponse::success([
                'payment_url' => $result['payment_url'],
                'payment_id'  => $result['payment_id']
            ], 'تم توليد رابط الدفع بنجاح.');
        } catch (PaymentExceedsBalanceException $e) {
            return ApiResponse::error($e->getMessage(), 400, 'Remaining balance: ' . $e->getRemainingBalance());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
    public function cancelPayment($payment_id)
    {
      

        try {
            $payment = $this->paymentService->cancelPayment($payment_id);
            return ApiResponse::success($payment, 'تم إلغاء الدفع بنجاح.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
