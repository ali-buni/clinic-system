<?php

namespace App\Services;

use App\Http\Resources\userResource;
use App\Models\User;
use App\Traits\HandleUserImage;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use HandleUserImage;
    public function info()
    {
        $user = Auth::user();

        if (!$user) {
            return ApiResponse::error('user not found', 404);
        }
        return ApiResponse::success(new userResource($user));
    }

    public function updateImage($image)
    {
        $user = Auth::user();

        if (!$user) {
            return ApiResponse::error('user not found', 404);
        }

        $this->deleteUserImage($user->profile_image);
        $path = $this->uploadUserImage($image);
        $user->update(['profile_image' => $path]);

        return ApiResponse::success(new userResource($user), 'Profile image updated successfully.');
    }

    public function getImageUrl()
    {
        $user = Auth::user();

        if (!$user) {
            return ApiResponse::error('user not found', 404);
        }

        return ApiResponse::success([
            'profile_image_url' => $this->getUserImageUrl($user->profile_image),
        ]);
    }
}
