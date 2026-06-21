<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PhoneTest extends TestCase
{
    const DOMAIN = 'phone';

    public function test_update_phone_sends_code()
    {
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->uri('/clinic-system/phone/update'),
            ['phone' => '0911111111'],
            $this->authHeaders($token)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Verification code sent to your new phone number.']);
        $this->saveFixture(self::DOMAIN, 'update-send-code-first-time', $response);
    }

    public function test_verify_phone_update_success()
    {
        $user = User::factory()->create(['phone' => '0912345678']);
        $token = $user->createToken('test')->plainTextToken;

        Cache::put("phone_update:{$user->id}", [
            'code' => Hash::make('123456'),
            'new_phone' => '0999999999',
            'attempts' => 0,
        ], now()->addMinutes(15));

        $response = $this->postJson(
            $this->uri('/clinic-system/phone/verify-update'),
            ['code' => '123456'],
            $this->authHeaders($token)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '0999999999',
        ]);
        $this->saveFixture(self::DOMAIN, 'verify-update-success', $response);
    }

    public function test_update_phone_validation_fails()
    {
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->uri('/clinic-system/phone/update'),
            ['phone' => 'invalid'],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-error-validation', $response);
    }

    public function test_verify_phone_update_no_pending_request()
    {
        $user = User::factory()->create(['phone' => '0912345678']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->uri('/clinic-system/phone/verify-update'),
            ['code' => '000000'],
            $this->authHeaders($token)
        );

        $response->assertStatus(500);
        $this->saveFixture(self::DOMAIN, 'verify-update-error-no-request', $response);
    }

    public function test_update_phone_unauthenticated()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/phone/update'),
            ['phone' => '0911111111']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'update-error-unauthorized', $response);
    }
}
