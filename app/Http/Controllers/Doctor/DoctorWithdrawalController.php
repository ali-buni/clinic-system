<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\SetupStripeRequest;
use App\Http\Requests\Doctor\WithdrawRequest;
use App\Models\Doctor;
use App\Services\ApiResponse;
use App\Services\DoctorWithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
            Log::error('store withdrawal failed', ['exception' => $e]);
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
            Log::error('getBalance failed', ['exception' => $e]);
            return ApiResponse::error('Server error', 500);
        }
    }

    public function setupStripe(SetupStripeRequest $request): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return ApiResponse::error('Doctor profile not found', 404);
            }

            $doctor->update([
                'stripe_connected_account_id' => $request->validated('stripe_account_id'),
            ]);

            return ApiResponse::success(
                null,
                'Stripe account linked successfully'
            );
        } catch (\Exception $e) {
            Log::error('setupStripe failed', ['exception' => $e]);
            return ApiResponse::error('Server error', 500);
        }
    }
}
