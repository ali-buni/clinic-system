<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Services\ApiResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function registerDeviceToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios',
        ]);

        FcmToken::updateOrCreate([
            'user_id' => auth()->id(),
            'fcm_token' => $validated['fcm_token'],
        ], [
            'device_type' => $validated['device_type'] ?? null,
        ]);

        return ApiResponse::success(null, 'The receiving device has been successfully linked to the notification system.');
    }
}
