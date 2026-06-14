<?php

namespace App\Http\Controllers;

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

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ], [
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.exists' => 'Phone number does not exist',
            'password.required' => 'Current password is required',
            'password.string' => 'Current password must be a string',
            'password.min' => 'Current password must be at least 8 characters',
            'new_password.required' => 'New password is required',
            'new_password.string' => 'New password must be a string',
            'new_password.min' => 'New password must be at least 8 characters',
        ]);

        $user = User::query()->where('phone', $validated['phone'])->first();
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
        $credentials = $request->only('phone', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->api->error('invalid credentials.', 401);
        }

        $user = User::query()->where('phone', $request->phone)->first();
        if (!$user) {
            return $this->api->error('no user found');
        }
        // return $this->verification->sendVerificationCode($user);
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->api->success(['token' => $token], 'Phone number verified successfully.');
    }
}
