<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponse;
use App\Traits\HandleUserImage;
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
            ->orWhere('email', $googleUser->email)
            ->first();

        if (!$user) {
            $image = $this->uploadUserImageFromUrl($googleUser->getAvatar());
            $user = User::create([
                'fname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'profile_image' => $image,
                'password' => Hash::make(Str::random(16))
            ]);
        } else {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            if ($user->profile_image === $this->defaultUserImage() || !$user->profile_image) {
                $this->deleteUserImage($user->profile_image);
                $user->update(['profile_image' => $this->uploadUserImageFromUrl($googleUser->getAvatar())]);
            }
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
            'role' => $role
            // 'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
