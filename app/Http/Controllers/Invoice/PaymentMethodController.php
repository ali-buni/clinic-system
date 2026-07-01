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

    public function destroy(Payment_method $payment_method): JsonResponse
    {
        $this->paymentMethodService->deleteMethod($payment_method->id);

        return ApiResponse::success(null, 'تم حذف وإلغاء خيار الدفع من النظام بنجاح.');
    }
}
