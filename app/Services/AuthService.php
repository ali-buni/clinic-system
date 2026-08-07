<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\User;
use App\Models\Verification_code;
use App\Notifications\SendPasswordResetCode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AuthService
{
    public function __construct(
        private ApiResponse $apiResponse,
    ) {}

    public function signOut(Request $request)
    {
        try {
            $this->revokeToken($request);

            LogActivityJob::dispatch('auth', 'User logged out', get_class($request->user()), $request->user()->id, $request->user());

            return $this->apiResponse->success(null, 'Logged out successfully');
        } catch (Exception $e) {
            Log::error('Failed to log out: '.$e->getMessage());

            return $this->apiResponse->error('Failed to log out', 500);
        }
    }

    private function revokeToken(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                Log::channel('structured')->warning('refreshToken - unauthenticated');

                return $this->apiResponse->error('Unauthenticated', 401);
            }

            $newToken = $user->createToken('auth_token')->plainTextToken;
            $request->user()?->currentAccessToken()?->delete();

            LogActivityJob::dispatch('auth', 'token refreshed', get_class($user), $user->id, null, [], 'updated');

            return $this->apiResponse->success(['auth_token' => $newToken], 'Token refreshed successfully');
        } catch (Exception $e) {
            Log::channel('structured')->error('Token refresh failed', ['error' => $e->getMessage()]);

            return $this->apiResponse->error('Failed to refresh token', 500);
        }
    }

    public function resetPassword(string $newPassword, string $email)
    {
        DB::beginTransaction();
        try {
            $user = User::byEmail($email)->first();
            $user->password = Hash::make($newPassword);
            $user->save();

            $user->tokens()->delete();

            DB::commit();

            LogActivityJob::dispatch('auth', 'password reset', get_class($user), $user->id, null, [], 'updated');

            return $this->apiResponse->success(null, 'the password is reset');
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('structured')->error('resetPassword failed', ['email' => $email, 'error' => $e->getMessage()]);

            return $this->apiResponse->error('error occurred in reset the password. Please try again.');
        }
    }

    public function forgotPassword(string $email): JsonResponse
    {
        $user = User::byEmail($email)->first();

        if (! $user) {
            Log::channel('structured')->warning('forgotPassword - email not found', ['email' => $email]);

            return $this->apiResponse->error('No account found with this email.', 404);
        }

        $code = random_int(100000, 999999);

        DB::transaction(function () use ($user, $code, $email) {
            Verification_code::create([
                'user_id' => $user->id,
                'type' => 'email_reset',
                'sent_to' => $email,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(15),
            ]);
        });

        Notification::route('mail', $email)
            ->notify(new SendPasswordResetCode($code));

        LogActivityJob::dispatch('auth', 'password reset code sent', get_class($user), $user->id, null, [], 'updated');

        return $this->apiResponse->success(null, 'Password reset code sent to your email.');
    }

    public function resetWithCode(string $email, string $code, string $newPassword): JsonResponse
    {
        $user = User::byEmail($email)->first();

        if (! $user) {
            Log::channel('structured')->warning('resetWithCode - user not found', ['email' => $email]);

            return $this->apiResponse->error('No account found.', 404);
        }

        $verification = Verification_code::where('user_id', $user->id)
            ->where('type', 'email_reset')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $verification) {
            Log::channel('structured')->warning('resetWithCode - no valid code', ['user_id' => $user->id]);

            return $this->apiResponse->error('No valid reset code found. Please request a new one.');
        }

        if ($verification->failed_attempts >= 5) {
            $verification->delete();
            Log::channel('structured')->warning('resetWithCode - too many failed attempts', ['user_id' => $user->id]);

            return $this->apiResponse->error('Too many failed attempts. Please request a new reset code.');
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('failed_attempts');
            $remainingAttempts = 5 - $verification->failed_attempts;

            return $this->apiResponse->error("Invalid reset code. {$remainingAttempts} attempts remaining.");
        }

        DB::transaction(function () use ($user, $newPassword, $verification) {
            $user->update(['password' => Hash::make($newPassword)]);
            $user->tokens()->delete();
            $verification->delete();
        });

        LogActivityJob::dispatch('auth', 'password reset with code', get_class($user), $user->id, null, [], 'updated');

        return $this->apiResponse->success(null, 'Password has been reset successfully.');
    }
}
