<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private VerificationService $verification
    ) {}

    public function resendVerificationCode(ResendVerificationRequest $request)
    {
        $validated = $request->validated();

        $user = User::byEmail($validated['login'])->first();

        if (!$user) {
            return $this->api->error('no user found!');
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return $this->api->error('invalid credentials.', 401);
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
