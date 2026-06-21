<?php

namespace App\Http\Controllers;

use App\Services\ApiResponse;
use App\Services\UserPhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPhoneController extends Controller
{
    public function __construct(
        private UserPhoneService $userPhoneService,
    ) {}

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|digits:10|starts_with:09',
        ]);

        $user = Auth::user();

        return $this->userPhoneService->updatePhone($user, $request->phone);
    }

    public function verifyPhoneUpdate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|digits:6',
        ]);

        $user = Auth::user();

        return $this->userPhoneService->verifyPhoneUpdate($user, $request->code);
    }
}
