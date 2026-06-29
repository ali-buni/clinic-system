<?php

namespace App\Http\Controllers;

use App\Services\PaymentMethodService;
use App\Services\ApiResponse; // الـ Helper تبعك
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    protected $paymentMethodService;

    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * 1. جلب قائمة بكل طرق الدفع النشطة
     * الرد يمرر المصفوفة جوات الـ data
     */
    public function getPaymentMethods(): JsonResponse
    {
        $methods = $this->paymentMethodService->getActiveMethods();

        return ApiResponse::success($methods, 'تم جلب طرق الدفع بنجاح.');
    }

    
    public function addPaymentMethod(Request $request): JsonResponse
    {
        $request->validate([
            'ar_name' => 'required|string|max:255',
            'en_name' => 'required|string|max:255',
        ]);

        $method = $this->paymentMethodService->createMethod($request->all());

        return ApiResponse::success(
            ['payment_method_id' => $method->id], 
            'تم إضافة طريقة الدفع بنجاح.', 
            201
        );
    }

    /**
     * 3. حذف طريقة دفع معينة
     * الرد يمرر null في الـ data طالما تمت العملية بنجاح
     */
    public function deletePaymentMethod($id): JsonResponse
    {
        $this->paymentMethodService->deleteMethod($id);

        return ApiResponse::success(null, 'تم حذف وإلغاء خيار الدفع من النظام بنجاح.');
    }
}