<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    public function __construct(
        private ApiResponse $apiResponse,
        private ActivityLogService $activityLog,
    ) {}

    public function signOut(Request $request)
    {
        try {
            $this->revokeToken($request);

            $this->activityLog->log('User logged out', 'User logged out', new User(), $request->user());
            return $this->apiResponse->success(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return $this->apiResponse->error('Failed to log out', 500, ['message' => $e->getMessage()]);
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
            if (!$user) {
                return $this->apiResponse->error('Unauthenticated', 401);
            }

            $newToken = $user->createToken('auth_token')->plainTextToken;
            $user->currentAccessToken()->delete();

            return $this->apiResponse->success(['auth_token' => $newToken], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->apiResponse->error('Failed to refresh token', 500, ['message' => $e->getMessage()]);
        }
    }

    public function resetPassword(string $newPassword, string $phone)
    {
        DB::beginTransaction();
        try {
            $user = User::query()->where('phone', $phone)->first();
            $user->password = Hash::make($newPassword);
            $user->save();

            DB::commit();
            return $this->apiResponse->success(null, 'the password is reseted');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiResponse->error('error ocurred in resest the password. Please try again.');
        }
    }
}
