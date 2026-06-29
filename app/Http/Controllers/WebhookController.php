<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\PaymentService;
use App\Services\ApiResponse; // 👈 استدعاء كلاس الرد الموحد هنا أيضاً
use Stripe\Stripe;

class WebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handleStripeWebhook(Request $request): JsonResponse
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        
        $payload = $request->getContent();
        $event = json_decode($payload, true);

        // 🛡️ حماية الكود وإرجاع الخطأ عبر الـ ApiResponse
        if (!$event || !isset($event['type'])) {
            return ApiResponse::error('البيانات المرسلة غير صالحة.', 400);
        }

        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data']['object'];
            $invoiceId = $session['metadata']['invoice_id'] ?? null;
            $paymentId = $session['metadata']['payment_id'] ?? null;

            if ($invoiceId && $paymentId) {
                // استدعاء السيرفس لتنفيذ المنطق المالي
                $this->paymentService->confirmPayment($invoiceId, $paymentId);
            }
        }

        // 🎯 إرجاع رد النجاح الموحد لسترايب
        return ApiResponse::success(['status' => 'success'], 'تمت معالجة الويب هوك بنجاح.');
    }
}