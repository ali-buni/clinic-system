<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Stripe;

class WebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->getContent();
        $event = json_decode($payload, true);

        if (!$event || !isset($event['type'])) {
            return ApiResponse::error('البيانات المرسلة غير صالحة.', 400);
        }

        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data']['object'];
            $invoiceId = $session['metadata']['invoice_id'] ?? null;
            $paymentId = $session['metadata']['payment_id'] ?? null;

            if ($invoiceId && $paymentId) {
                $this->paymentService->confirmPayment($invoiceId, $paymentId);
            }
        }

        return ApiResponse::success(['status' => 'success'], 'تمت معالجة الويب هوك بنجاح.');
    }
}
