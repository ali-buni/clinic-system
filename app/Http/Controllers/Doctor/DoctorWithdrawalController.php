<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\WithdrawRequest;
use App\Models\Doctor;
use App\Services\ApiResponse;
use App\Services\DoctorWithdrawalService;
use App\Services\StripeConnectService;
use Illuminate\Http\JsonResponse;

class DoctorWithdrawalController extends Controller
{
    public function __construct(
        private readonly DoctorWithdrawalService $withdrawalService
    ) {}

    public function index(): JsonResponse
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found', 404);
        }

        $withdrawals = $doctor->withdrawals()
            ->with('approvedBy')
            ->latest()
            ->paginate(15);

        return ApiResponse::pagination($withdrawals, 'Withdrawals fetched successfully');
    }

    public function store(WithdrawRequest $request): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return ApiResponse::error('Doctor profile not found', 404);
            }

            $withdrawal = $this->withdrawalService->requestWithdrawal(
                $doctor,
                $request->validated('amount')
            );

            return ApiResponse::success(
                $withdrawal->load('doctor'),
                'Withdrawal request submitted successfully',
                201
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Server error', 500);
        }
    }

    public function getBalance(): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return ApiResponse::error('Doctor profile not found', 404);
            }

            $balance = $this->withdrawalService->getBalance($doctor);

            return ApiResponse::success($balance, 'Balance fetched successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Server error', 500);
        }
    }

    public function setupStripe(): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return ApiResponse::error('Doctor profile not found', 404);
            }

            $stripeService = app(StripeConnectService::class);

            if ($doctor->stripe_connected_account_id) {
                $onboardingUrl = $stripeService->generateAccountLink($doctor->stripe_connected_account_id);

                return ApiResponse::success(
                    ['onboarding_url' => $onboardingUrl],
                    'Stripe onboarding link generated'
                );
            }

            $accountId = $stripeService->createConnectedAccount($doctor);
            $onboardingUrl = $stripeService->generateAccountLink($accountId);

            return ApiResponse::success(
                ['onboarding_url' => $onboardingUrl],
                'Stripe connected account created'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Server error', 500);
        }
    }
}
