<?php

namespace App\Services;

use App\Http\Resources\userResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function info()
    {
        $user = Auth::user();

        if (!$user) {
            return ApiResponse::error('user not found', 404);
        }
        return ApiResponse::success(new userResource($user));
    }
}
