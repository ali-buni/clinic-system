<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class userController extends Controller
{
    public function __construct(private UserService $user_service) {}

    public function info()
    {
        return $this->user_service->info();
    }

    public function updateImage()
    {
        $data = request()->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        return $this->user_service->updateImage($data['profile_image']);
    }

    public function getImageUrl()
    {
        return $this->user_service->getImageUrl();
    }
}
