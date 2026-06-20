<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function registerDeviceToken(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Not implemented yet.']);
    }
}
