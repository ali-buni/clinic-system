<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification_code;
use App\Notifications\SendEmailVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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

    private function verifyEmail(User $user)
    {
        $user->email_verified_at = now();
        $user->save();
    }

    /**
     * Verifies a code (phone or email).
     */
    public function verify(User $user, string $code, string $type = 'phone')
    {
        $verification = Verification_code::where('user_id', $user->id)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $verification) {
            return $this->apiResponse->error('No verification code found. Please request a new one.');
        }

        if ($verification->failed_attempts >= 5) {
            $verification->delete();

            return $this->apiResponse->error('Maximum verification attempts reached. Please request a new code.');
        }
        if (! Hash::check($code, $verification->code_hash)) {
            $verification->failed_attempts += 1;
            $verification->save();

            return $this->apiResponse->error('Invalid or expired verification code.');
        }

        if ($type === 'email') {
            $this->verifyEmail($user);
        } else {
            $this->verifyPhone($user);
        }

        $verification->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $role = $user->getRoleNames()->first() ?? 'patient';

        $roleId = $user->hasRole('owner') ? $user->clinicOwner?->id : ($user->hasRole('doctor') ? $user->doctorProfile?->id : ($user->hasRole('patient') ? $user->patientProfile?->id : ($user->hasRole('secretary') ? $user->secretaryProfile?->id : null)));
        $data = [
            'token' => $token,
            'id' => $user->id,
            'role' => $role,
            'name' => $user->fname . ' ' . $user->lname,
            'role_id' => $roleId,
        ];

        if ($role === 'owner') {
            $data['clinic_id'] = $user->clinicOwner?->id;
        }

        return $this->apiResponse->success(
            $data,
            ucfirst($type) . ' verified successfully.'
        );
    }

    /**
     * Sends a verification code to the user's phone number or email.
     */
    public function sendVerificationCode(User $user, string $type): JsonResponse
    {
        // Validate type
        if (! in_array($type, ['phone', 'email'])) {
            return $this->apiResponse->error('Invalid verification type. Must be "phone" or "email".', 400);
        }

        $user_id = $user->id;
        $sentTo = $type === 'phone' ? $user->phone : $user->email;

        // Check if the contact method exists
        if (empty($sentTo)) {
            return $this->apiResponse->error(
                $type === 'phone' ? 'User has no phone number.' : 'User has no email address.',
                400
            );
        }

        $attemptsKey = "verification_attempts:{$user_id}";
        $raw = Cache::get($attemptsKey, ['count' => 0, 'started_at' => null]);
        $attemptData = is_array($raw) ? $raw : ['count' => (int) $raw, 'started_at' => null];
        $attempts = $attemptData['count'];

        if ($attempts >= self::Max_attempts) {
            $remainingSeconds = $attemptData['started_at']
                ? max(0, $attemptData['started_at'] + 3600 - now()->timestamp)
                : 3600;

            return $this->apiResponse->error(
                'Too many attempts. Please try again later.',
                429,
                ['retry_after_minutes' => (int) ceil($remainingSeconds / 60)]
            );
        }

        // Check if a code was sent recently (60 second codedown)
        $recent = Verification_code::where('user_id', $user->id)
            ->where('type', $type)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return $this->apiResponse->error(
                'A verification code was sent recently. Please wait before requesting another one.',
                429
            );
        }

        $code = random_int(100000, 999999);

        try {
            DB::beginTransaction();

            // Delete old verification codes for this type
            Verification_code::where('user_id', $user->id)
                ->where('type', $type)
                ->delete();

            // Create new verification code
            Verification_code::create([
                'user_id' => $user->id,
                'type' => $type,
                'sent_to' => $sentTo,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send the code based on type
            if ($type === 'phone') {
                // TODO: send sms
                // Send SMS (implement your SMS sending logic here)
                // Example: $this->sendSms($user->phone, $code);
                logger()->info("Sent verification code {$code} to phone {$user->phone}");
            } else {
                // Send email
                Notification::route('mail', $user->email)
                    ->notify(new SendEmailVerificationCode($code));
                logger()->info("Sent verification code {$code} to email {$user->email}");
            }

            // Increment attempt counter
            Cache::put($attemptsKey, [
                'count' => $attempts + 1,
                'started_at' => $attemptData['started_at'] ?? now()->timestamp,
            ], 3600);

            DB::commit();

            return $this->apiResponse->success(
                null,
                ucfirst($type) . ' verification code sent successfully.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('structured')->error("Failed to send {$type} verification code: " . $e->getMessage());

            return $this->apiResponse->error(
                'Failed to send verification code. Please try again.',
                500
            );
        }
    }
}
