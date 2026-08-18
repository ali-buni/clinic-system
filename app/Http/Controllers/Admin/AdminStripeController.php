<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\SetupStripeRequest;
use App\Models\Doctor;
use App\Services\ApiResponse;
use App\Services\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminStripeController extends Controller
{
    public function link(SetupStripeRequest $request, int $id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);

            $stripeService = app(StripeConnectService::class);
            $stripeService->linkAccount($doctor, $request->validated('stripe_account_id'));

            return ApiResponse::success(null, 'Stripe account linked successfully');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('admin linkStripe failed', ['exception' => $e]);
            return ApiResponse::error('Server error', 500);
        }
    }
}
