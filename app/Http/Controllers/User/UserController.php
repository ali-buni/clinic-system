<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(private UserService $user_service) {}

    public function info()
    {
        return $this->user_service->info();
    }

    public function updateImage()
    {
        $data = request()->validate([
            'profile_image' => [
                'required',
                'image',
                'mimes:jpeg,png,webp',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if (! @getimagesize($value->getRealPath())) {
                        $fail('The profile image must be a valid image file.');
                    }
                },
            ],
        ]);

        return $this->user_service->updateImage($data['profile_image']);
    }

    public function getImageUrl()
    {
        return $this->user_service->getImageUrl();
    }
}
