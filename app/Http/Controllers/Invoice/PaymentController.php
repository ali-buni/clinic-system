<?php

namespace App\Http\Controllers\Invoice;

use App\Exceptions\PaymentExceedsBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Requests\Payment\RefundPaymentRequest;
use App\Http\Resources\Invoice\PaymentResource;
use App\Models\Doctor;
use App\Models\Payment;
use App\Services\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
        ]);

        $payments = Payment::where('invoice_id', $request->invoice_id)
            ->with('paymentMethod')
            ->latest()
            ->get();

        return ApiResponse::success(
            PaymentResource::collection($payments),
            'payments fetched successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with('paymentMethod')->find($id);

        if (! $payment) {
            return ApiResponse::error('payment not found', 404);
        }

        return ApiResponse::success(
            new PaymentResource($payment),
            'payment fetched successfully'
        );
    }

    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->paymentService->processPayment(
                $validated['invoice_id'],
                $validated['payment_method_id'],
                $validated['amount'],
            );

            if (isset($result['payment_url']) && ! $this->isValidStripeUrl($result['payment_url'])) {
                Log::channel('structured')->error('Invalid Stripe payment URL received', [
                    'invoice_id' => $validated['invoice_id'],
                    'url' => $result['payment_url'],
                ]);

                return ApiResponse::error('failed to create payment session', 500);
            }

            return ApiResponse::success($result);
        } catch (PaymentExceedsBalanceException $e) {
            return ApiResponse::error(
                'the paid amount is larger than the remaining balance',
                400,
                ['remaining_balance' => $e->getRemainingBalance()]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::channel('structured')->error('Payment processing failed', [
                'invoice_id' => $validated['invoice_id'],
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('server error', 500);
        }
    }

    public function refund(RefundPaymentRequest $request): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (! $doctor) {
                return ApiResponse::error('unauthorized: doctor profile not found', 403);
            }

            $validated = $request->validated();
            $results = [];
            $errors = [];

            foreach ($validated['refunds'] as $index => $refundData) {
                $payment = Payment::with(['invoice.appointment'])->find($refundData['payment_id']);

                if (! $payment) {
                    $errors[$index] = ['payment_id' => $refundData['payment_id'], 'error' => 'payment not found'];

                    continue;
                }

                $appointment = $payment->invoice?->appointment;
                if (! $appointment || $appointment->doctor_id !== $doctor->id) {
                    $errors[$index] = ['payment_id' => $refundData['payment_id'], 'error' => 'unauthorized: not your appointment'];

                    continue;
                }

                $refundable = $payment->getRefundableAmount();
                if ($refundable <= 0) {
                    $errors[$index] = ['payment_id' => $refundData['payment_id'], 'error' => 'no refundable amount remaining'];

                    continue;
                }

                if ($refundData['amount'] > $refundable) {
                    $errors[$index] = ['payment_id' => $refundData['payment_id'], 'error' => "refund amount {$refundData['amount']} exceeds refundable {$refundable}"];

                    continue;
                }

                $refund = $this->paymentService->refundPayment(
                    $refundData['payment_id'],
                    $refundData['amount'],
                    $refundData['reason'] ?? null,
                    auth()->id()
                );

                $results[] = [
                    'refund_id' => $refund->id,
                    'payment_id' => $refund->payment_id,
                    'amount' => (float) $refund->amount,
                    'reason' => $refund->reason,
                    'refundable_remaining' => (float) $payment->fresh()->getRefundableAmount(),
                    'invoice_status' => $payment->invoice->fresh()->status,
                ];
            }

            if (empty($results) && ! empty($errors)) {
                return ApiResponse::error('all refunds failed', 422, ['errors' => $errors]);
            }

            return ApiResponse::success(
                ['refunds' => $results, 'errors' => $errors],
                count($results).' refund(s) processed'.(empty($errors) ? '' : ' with '.count($errors).' error(s)')
            );
        } catch (\Exception $e) {
            Log::channel('structured')->error('Batch refund failed', ['error' => $e->getMessage()]);

            return ApiResponse::error('server error', 500);
        }
    }

    public function destroy(int $paymentId): JsonResponse
    {
        try {
            $this->paymentService->cancelPayment($paymentId);

            return ApiResponse::success(null, 'the payment is canceled');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::channel('structured')->error('Payment cancellation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('server error', 500);
        }
    }

    private function isValidStripeUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parsed = parse_url($url);

        if (! $parsed || ! isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }

        $validHosts = ['checkout.stripe.com', 'payments.stripe.com'];
        $isValidHost = in_array($parsed['host'], $validHosts)
            || str_ends_with($parsed['host'], '.stripe.com');

        return $parsed['scheme'] === 'https' && $isValidHost;
    }
}
