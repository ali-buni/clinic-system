<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Resources\Invoice\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Exceptions\PaymentExceedsBalanceException;
use App\Services\PaymentService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function store(Invoice $invoice, ProcessPaymentRequest $request): JsonResponse
    {
        try {
            if ($request->payment_method_id == 1) {
                $updatedInvoice = $this->paymentService->processCashPayment(
                    $invoice->id,
                    $request->amount
                );

                return ApiResponse::success([
                    'invoice_id'        => $updatedInvoice->id,
                    'status'            => $updatedInvoice->status,
                    'remaining_balance' => number_format($updatedInvoice->getRemainingBalance(), 2, '.', ''),
                ], 'تم تسجيل الدفع النقدي وتحديث الفاتورة بنجاح.');
            }

            $result = $this->paymentService->createStripeSession(
                $invoice->id,
                $request->payment_method_id,
                $request->amount
            );

            return ApiResponse::success([
                'payment_url' => $result['payment_url'],
                'payment_id'  => $result['payment_id'],
            ], 'تم توليد رابط الدفع بنجاح.');
        } catch (PaymentExceedsBalanceException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                400,
                ['remaining_balance' => $e->getRemainingBalance()]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function destroy(Payment $payment): JsonResponse
    {
        try {
            $result = $this->paymentService->cancelPayment($payment->id);

            return ApiResponse::success($result, 'تم إلغاء الدفع بنجاح.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
