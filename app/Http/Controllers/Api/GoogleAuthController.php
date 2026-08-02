<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadAndStoreImageJob;
use App\Models\PatientInfo;
use App\Models\User;
use App\Services\ApiResponse;
use App\Traits\HandleUserImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    use HandleUserImage;

    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return ApiResponse::success(['url' => $url]);
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return ApiResponse::error('Invalid credentials from Google.', 401);
        }

        $user = User::where('google_id', $googleUser->id)
            ->orWhere(fn ($q) => $q->byEmail($googleUser->email))
            ->first();

        if (! $user) {
            $user = DB::transaction(function () use ($googleUser) {
                $user = User::create([
                    'fname' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'profile_image' => $this->defaultUserImage(),
                    'password' => Hash::make(Str::random(16)),
                ]);
                $user->assignRole('patient');
                PatientInfo::create(['user_id' => $user->id]);

                return $user;
            });

            DownloadAndStoreImageJob::dispatch($user->id, $googleUser->getAvatar());
        } elseif (! $user->hasRole('patient')) {
            return ApiResponse::error('UnAuthorized', 401);
        }

        if (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }
        if ($user->profile_image === $this->defaultUserImage() || ! $user->profile_image) {
            DownloadAndStoreImageJob::dispatch($user->id, $googleUser->getAvatar());
        }
        $user->tokens()->delete();
        $token = $user->createToken('google-auth-token')->plainTextToken;

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken(string $token, User $user)
    {
        $role = $user->getRoleNames()->first();

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'id' => $user->id,
            'name' => $user->fname,
            'role' => $role,
            // 'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
