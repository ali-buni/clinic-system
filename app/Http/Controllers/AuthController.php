<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\AuthService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private AuthService $authService,
        private VerificationService $verification
    ) {}

    public function signOut(Request $request)
    {
        return $this->authService->signOut($request);
    }

    public function refreshToken(Request $request)
    {
        return $this->authService->refreshToken($request);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = User::byPhone($validated['phone'])->first();
        if (!$user) {
            return $this->api->error('no account found!');
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return $this->api->error('Current password is incorrect', 400);
        }

        return $this->authService->resetPassword($validated['new_password'], $user->phone);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|digits_between:10,13|starts_with:09',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('phone', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->api->error('invalid credentials.', 401);
        }

        $user = Auth::user();
        return $this->verification->sendVerificationCode($user);
    }
}
