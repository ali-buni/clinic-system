<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Resources\Invoice\PaymentMethodResource;
use App\Models\Payment_method;
use App\Services\PaymentMethodService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function __construct(
        protected PaymentMethodService $paymentMethodService
    ) {}

    public function index(): JsonResponse
    {
        $methods = $this->paymentMethodService->getActiveMethods();

        return ApiResponse::success(
            PaymentMethodResource::collection($methods),
            'تم جلب طرق الدفع بنجاح.'
        );
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $method = $this->paymentMethodService->createMethod($request->validated());

        return ApiResponse::success(
            new PaymentMethodResource($method),
            'تم إضافة طريقة الدفع بنجاح.',
            201
        );
    }

    public function stop(int $id): JsonResponse
    {
        try {
            $payment = Payment_method::find($id);
            if (!$payment) {
                return ApiResponse::error('no payment method found', 404);
            }
            $this->paymentMethodService->stopMethod($payment);

            return ApiResponse::success(
                null,
                'تم إيقاف طريقة الدفع بنجاح.'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Server Error');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $payment = Payment_method::find($id);
            if (!$payment) {
                return ApiResponse::error('no payment method found', 404);
            }
            $this->paymentMethodService->deleteMethod($payment);

            return ApiResponse::success(
                null,
                'تم حذف طريقة الدفع بنجاح.'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Server Error');
        }
    }
}
