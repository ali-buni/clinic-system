<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterPatientRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetPasswordWithCodeRequest;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\AuthService;
use App\Services\PatientRegistrationService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private AuthService $authService,
        private VerificationService $verification,
        private PatientRegistrationService $patientRegistration,
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
        $user = User::byEmail($validated['email'])->first();
        if (!$user) {
            return $this->api->error('no account found!');
        }
        if (!Hash::check($validated['password'], $user->password)) {
            return $this->api->error('Current password is incorrect', 400);
        }
        return $this->authService->resetPassword($validated['new_password'], $user->email);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $credentials = [
            'email'    => $request->login,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return $this->api->error('invalid credentials.', 401);
        }

        $user = Auth::user();
        return $this->verification->sendVerificationCode($user, 'email');
    }

    public function register(RegisterPatientRequest $request)
    {
        return $this->patientRegistration->register($request->validated());
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request->validated('email'));
    }

    public function resetWithCode(ResetPasswordWithCodeRequest $request)
    {
        $validated = $request->validated();
        return $this->authService->resetWithCode(
            $validated['email'],
            $validated['code'],
            $validated['password']
        );
    }
}
