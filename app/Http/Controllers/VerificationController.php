<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResendVerificationRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private VerificationService $verification
    ) {}

    public function resendVerificationCode(ResendVerificationRequest $request)
    {
        $validated = $request->validated();

        $credentials = [
            'email'    => $request->login,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return $this->api->error('invalid credentials.', 401);
        }
        $user = User::byEmail($validated['login'])->first();
        if (!$user) {
            return $this->api->error('no user found!');
        }
        return $this->verification->sendVerificationCode($user, 'email');
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $validated = $request->validated();

        $user = User::byEmail($validated['login'])->first();

        if (!$user) {
            return $this->api->error('no user found!');
        }

        $type = $validated['type'];

        return $this->verification->verify($user, $validated['code'], $type);
    }
}
