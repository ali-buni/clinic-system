<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ApiResponse;
use App\Services\VerificationService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private VerificationService $verification
    ) {}

    public function resendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ], [
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.exists' => 'Phone number does not exist',
        ]);

        $user = User::query()->where('phone', $validated['phone'])->first();
        if (!$user) {
            return $this->api->error('no user found!');
        }

        return $this->verification->sendVerificationCode($user);
    }

    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'code' => 'required|string|digits:6'
        ], [
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.exists' => 'Phone number does not exist',

            'code.required' => 'Code number is required',
            'code.string' => 'Code number must be a string',
            'code.digits' => 'Code digits is not 6',
        ]);


        $user = User::query()->where('phone', $validated['phone'])->first();
        if (!$user) {
            return $this->api->error('no user found!');
        }

        return $this->verification->verify($user, $validated['code']);
    }
}
