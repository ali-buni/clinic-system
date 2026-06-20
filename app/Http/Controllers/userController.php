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
}
