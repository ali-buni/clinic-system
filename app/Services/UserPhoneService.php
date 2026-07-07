<?php

namespace App\Services;

use App\Events\SendMsgEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserPhoneService
{
    public function __construct(
        private ApiResponse $apiResponse,
    ) {}

    public function updatePhone(User $user, string $newPhone): JsonResponse
    {
        $code = random_int(100000, 999999);
        $cacheKey = "phone_update:{$user->id}";

        Cache::put($cacheKey, [
            'code' => Hash::make($code),
            'new_phone' => $newPhone,
            'attempts' => 0,
        ], now()->addMinutes(15));

        try {
            event(new SendMsgEvent(
                $newPhone,
                config('app.name').": Your verification code is: {$code}"
            ));
        } catch (\Exception $e) {
            Log::channel('structured')->error('Failed to send phone update SMS: '.$e->getMessage());
        }

        return $this->apiResponse->success(
            ['new_phone' => $newPhone],
            'Verification code sent to your new phone number.'
        );
    }

    public function verifyPhoneUpdate(User $user, string $code): JsonResponse
    {
        $cacheKey = "phone_update:{$user->id}";
        $data = Cache::get($cacheKey);

        if (! $data) {
            return $this->apiResponse->error('No verification code found. Please request a new one.');
        }

        if (($data['attempts'] ?? 0) >= 5) {
            Cache::forget($cacheKey);

            return $this->apiResponse->error('Maximum verification attempts reached. Please request a new code.');
        }

        if (! Hash::check($code, $data['code'])) {
            $data['attempts'] = ($data['attempts'] ?? 0) + 1;
            Cache::put($cacheKey, $data, now()->addMinutes(15));

            return $this->apiResponse->error('Invalid verification code.');
        }

        $user->phone = $data['new_phone'];
        $user->save();

        Cache::forget($cacheKey);

        return $this->apiResponse->success(null, 'Phone number updated successfully.');
    }
}
