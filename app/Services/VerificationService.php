<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification_code;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    private const Max_attempts = 5;

    public function __construct(private ApiResponse $apiResponse) {}

    /**
     * Verifies the user's phone number.
     */
    private function verifyPhone(User $user)
    {
        $user->phone_verified_at = now();
        $user->save();
    }

    /**
     * Verifies the user's phone number.
     */
    public function verify(User $user, string $code)
    {
        $verification = Verification_code::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$verification) {
            return $this->apiResponse->error('No verification code found. Please request a new one.');
        }

        if ($verification->failed_attempts >= 5) {
            //delete the verification code
            $verification->delete();
            return $this->apiResponse->error('Maximum verification attempts reached. Please request a new code.');
        }
        if (!Hash::check($code, $verification->code_hash)) {
            $verification->failed_attempts += 1;
            $verification->save();
            return $this->apiResponse->error('Invalid or expired verification code.');
        }
        $this->verifyPhone($user);

        $role = 'secretary';
        $id = $user->id;
        if ($user->hasRole('doctor')) {
            $role = 'doctor';
        } else if ($user->hasRole('owner')) {
            $role = 'owner';
        }
        $verification->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->apiResponse->success(
            [
                'token' => $token,
                'id' => $id,
                'role' => $role,
                'name' => $user->fname . ' ' . $user->lname,
            ],
            'Phone number verified successfully.'
        );
    }

    /**
     * Sends a verification code to the user's phone number. Returns true if sent successfully, false otherwise.
     */
    public function sendVerificationCode(User $user)
    {
        $user_id = $user->id;

        $attemptsKey = "{$user_id}.{$user->phone}";
        if (!Cache::has($attemptsKey)) {
            Cache::add($attemptsKey, 1, 3600);
            $attempts = 1;
        } else {
            $attempts = Cache::increment($attemptsKey, 1);
        }

        if ($attempts > self::Max_attempts) {
            $remainingTime = Cache::ttl($attemptsKey);
            return $this->apiResponse->error(
                'to many attemps. Please try again later.',
                429,
                ['retry_after_minut es' => $remainingTime]
            );
        }

        $recent = Verification_code::where('user_id', $user->id)
            ->where('created_at', '>', now()->subSeconds(60))->exists();

        if ($recent) {
            return $this->apiResponse->error('A verification code was sent recently. Please wait before requesting another one.');
        }

        $code = rand(100000, 999999);

        try {
            DB::beginTransaction();

            $verify = new verification_code();
            $verify->user_id = $user_id;
            $verify->type = 'phone';
            $verify->sent_to = $user->phone;
            $verify->code_hash = Hash::make($code);
            $verify->expires_at = now()->addMinutes(15);
            $verify->save();

            // event(new SendMsgEvent($user->phone, config('app.name') . ": Your verification code is: {$code}."));
            \Illuminate\Support\Facades\Log::info("Sent verification code to phone {$user->phone}");

            DB::commit();

            return $this->apiResponse->success(null, 'Verification code sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse->error('Failed to send verification code. Please try again.');
        }
    }
}
