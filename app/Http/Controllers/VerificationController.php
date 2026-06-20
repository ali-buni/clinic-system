<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResendVerificationRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\VerificationService;

class VerificationController extends Controller
{
    public function __construct(
        private ApiResponse $api,
        private VerificationService $verification
    ) {}

    public function resendVerificationCode(ResendVerificationRequest $request)
    {
        $validated = $request->validated();
        $user = User::byPhone($validated['phone'])->first();

        if (!$user) {
            return $this->api->error('no user found!');
        }

        return $this->verification->sendVerificationCode($user);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $validated = $request->validated();

        if (!empty($validated['email'])) {
            $user = User::byEmail($validated['email'])->first();
        } else {
            $user = User::byPhone($validated['phone'])->first();
        }

        if (!$user) {
            return $this->api->error('no user found!');
        }

        $type = $validated['type'] ?? (!empty($validated['email']) ? 'email' : 'phone');

        return $this->verification->verify($user, $validated['code'], $type);
    }
}
