<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AllApiAuthTest extends TestCase
{
    const AUTH_DOMAIN = 'auth';
    const VERIFICATION_DOMAIN = 'verification';
    const PHONE_DOMAIN = 'phone';
    const DEVICES_DOMAIN = 'devices';

    // ====================================================================
    // AUTH DOMAIN
    // ====================================================================

    // ---- POST /api/v1/clinic-system/login ----

    public function test_login_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/login'), [
            'login'    => $this->patientUser->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);
        $this->saveFixture(self::AUTH_DOMAIN, 'login-success', $response);
    }

    public function test_login_invalid_credentials()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/login'), [
            'login'    => $this->patientUser->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
        $this->saveFixture(self::AUTH_DOMAIN, 'login-error-invalid-credentials', $response);
    }

    public function test_login_user_not_found()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/login'), [
            'login'    => 'noone@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'login-error-not-found', $response);
    }

    public function test_login_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/login'), [
            'login'    => '',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'login-error-validation', $response);
    }

    public function test_login_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/login'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'login-error-missing-fields', $response);
    }

    // ---- POST /api/v1/clinic-system/register ----

    public function test_register_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/register'), [
            'fname'                 => 'New',
            'lname'                 => 'Patient',
            'email'                 => 'newpatient@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'dob'                   => '1990-01-01',
            'gender'                => 'male',
            'clinic_id'             => $this->clinic->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::AUTH_DOMAIN, 'register-success', $response);
    }

    public function test_register_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/register'), [
            'fname'                 => '',
            'email'                 => 'not-an-email',
            'password'              => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'register-error-validation', $response);
    }

    public function test_register_duplicate_email()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/register'), [
            'fname'                 => 'Duplicate',
            'lname'                 => 'User',
            'email'                 => 'existingpatient@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'dob'                   => '1990-01-01',
            'gender'                => 'male',
            'clinic_id'             => $this->clinic->id,
        ]);

        // First registration should succeed
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Second registration with same email returns 200 because CipherSweet encrypts differently
        $response2 = $this->postJson($this->v1uri('/clinic-system/register'), [
            'fname'                 => 'Duplicate',
            'lname'                 => 'User',
            'email'                 => 'existingpatient@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'dob'                   => '1990-01-01',
            'gender'                => 'male',
            'clinic_id'             => $this->clinic->id,
        ]);

        $response2->assertStatus(200);
        $this->saveFixture(self::AUTH_DOMAIN, 'register-duplicate-email-behavior', $response2);
    }

    public function test_register_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/register'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'register-error-missing-fields', $response);
    }

    public function test_register_password_mismatch()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/register'), [
            'fname'                 => 'Mismatch',
            'lname'                 => 'User',
            'email'                 => 'mismatch@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'different456',
            'dob'                   => '1990-01-01',
            'gender'                => 'male',
            'clinic_id'             => $this->clinic->id,
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'register-error-password-mismatch', $response);
    }

    // ---- POST /api/v1/clinic-system/forgot-password ----

    public function test_forgot_password_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/forgot-password'), [
            'email' => $this->patientUser->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::AUTH_DOMAIN, 'forgot-password-success', $response);
    }

    public function test_forgot_password_user_not_found()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/forgot-password'), [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(404);
        $this->saveFixture(self::AUTH_DOMAIN, 'forgot-password-error-not-found', $response);
    }

    public function test_forgot_password_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/forgot-password'), [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'forgot-password-error-validation', $response);
    }

    public function test_forgot_password_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/forgot-password'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'forgot-password-error-missing-fields', $response);
    }

    // ---- POST /api/v1/clinic-system/reset-password-with-code ----

    public function test_reset_with_code_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password-with-code'), [
            'email'                 => $this->patientUser->email,
            'code'                  => '123456',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-with-code-response', $response);
    }

    public function test_reset_with_code_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password-with-code'), [
            'email'                 => '',
            'code'                  => '123',
            'password'              => 'short',
            'password_confirmation' => 'not-matching',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-with-code-error-validation', $response);
    }

    public function test_reset_with_code_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password-with-code'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-with-code-error-missing-fields', $response);
    }

    public function test_reset_with_code_user_not_found()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password-with-code'), [
            'email'                 => 'nonexistent@test.com',
            'code'                  => '123456',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(404);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-with-code-error-not-found', $response);
    }

    public function test_reset_with_code_invalid_code()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password-with-code'), [
            'email'                 => $this->patientUser->email,
            'code'                  => '000000',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(500);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-with-code-error-invalid-code', $response);
    }

    // ---- POST /api/v1/clinic-system/signout ----

    public function test_signout_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/signout'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::AUTH_DOMAIN, 'signout-success', $response);
    }

    public function test_signout_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/signout'));

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'signout-error-unauthorized', $response);
    }

    public function test_signout_invalid_token()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/signout'),
            [],
            $this->authHeaders('invalid-token-12345')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'signout-error-invalid-token', $response);
    }

    // ---- POST /api/v1/clinic-system/reset-password (authenticated) ----

    public function test_reset_password_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/reset-password'),
            [
                'email'                    => $this->patientUser->email,
                'password'                 => 'password',
                'password_confirmation'    => 'password',
                'new_password'             => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-success', $response);
    }

    public function test_reset_password_wrong_current()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/reset-password'),
            [
                'email'                    => $this->patientUser->email,
                'password'                 => 'wrongpassword',
                'password_confirmation'    => 'wrongpassword',
                'new_password'             => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(400);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-error-wrong-current', $response);
    }

    public function test_reset_password_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/reset-password'),
            [
                'email'                    => '',
                'password'                 => '',
                'password_confirmation'    => '',
                'new_password'             => '',
                'new_password_confirmation' => '',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-error-validation', $response);
    }

    public function test_reset_password_missing_fields()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/reset-password'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-error-missing-fields', $response);
    }

    public function test_reset_password_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/reset-password'), [
            'email'                    => $this->patientUser->email,
            'password'                 => 'password',
            'password_confirmation'    => 'password',
            'new_password'             => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-error-unauthorized', $response);
    }

    public function test_reset_password_new_password_mismatch()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/reset-password'),
            [
                'email'                    => $this->patientUser->email,
                'password'                 => 'password',
                'password_confirmation'    => 'password',
                'new_password'             => 'newpass123',
                'new_password_confirmation' => 'different456',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::AUTH_DOMAIN, 'reset-password-error-new-password-mismatch', $response);
    }

    // ---- POST /api/v1/clinic-system/refresh-token ----

    public function test_refresh_token_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/refresh-token'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['auth_token']]);
        $this->saveFixture(self::AUTH_DOMAIN, 'refresh-token-success', $response);
    }

    public function test_refresh_token_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/refresh-token'));

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'refresh-token-error-unauthorized', $response);
    }

    public function test_refresh_token_invalid_token()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/refresh-token'),
            [],
            $this->authHeaders('invalid-token-12345')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'refresh-token-error-invalid-token', $response);
    }

    public function test_refresh_token_expired_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', ['*'], now()->subDay())->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/refresh-token'),
            [],
            $this->authHeaders($token)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::AUTH_DOMAIN, 'refresh-token-error-expired-token', $response);
    }

    // ====================================================================
    // VERIFICATION DOMAIN
    // ====================================================================

    // ---- POST /api/v1/clinic-system/verify-code ----

    public function test_verify_code_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/verify-code'), [
            'login' => $this->patientUser->email,
            'code'  => '123456',
            'type'  => 'email',
        ]);

        $response->assertStatus(200);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'verify-code-response', $response);
    }

    public function test_verify_code_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/verify-code'), [
            'login' => '',
            'code'  => '123',
            'type'  => 'invalid',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'verify-code-error-validation', $response);
    }

    public function test_verify_code_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/verify-code'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'verify-code-error-missing-fields', $response);
    }

    public function test_verify_code_user_not_found()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/verify-code'), [
            'login' => 'nonexistent@test.com',
            'code'  => '123456',
            'type'  => 'email',
        ]);

        $response->assertStatus(500);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'verify-code-error-not-found', $response);
    }

    public function test_verify_code_invalid_code()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/verify-code'), [
            'login' => $this->patientUser->email,
            'code'  => '000000',
            'type'  => 'email',
        ]);

        $response->assertStatus(500);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'verify-code-error-invalid-code', $response);
    }

    // ---- POST /api/v1/clinic-system/resend-code ----

    public function test_resend_code_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/resend-code'), [
            'login'    => $this->patientUser->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'resend-code-response', $response);
    }

    public function test_resend_code_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/resend-code'), [
            'login'    => '',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'resend-code-error-validation', $response);
    }

    public function test_resend_code_missing_fields()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/resend-code'), []);

        $response->assertStatus(422);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'resend-code-error-missing-fields', $response);
    }

    public function test_resend_code_invalid_credentials()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/resend-code'), [
            'login'    => $this->patientUser->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'resend-code-error-invalid-credentials', $response);
    }

    public function test_resend_code_user_not_found()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/resend-code'), [
            'login'    => 'noone@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::VERIFICATION_DOMAIN, 'resend-code-error-user-not-found', $response);
    }

    // ====================================================================
    // PHONE DOMAIN
    // ====================================================================

    // ---- POST /api/v1/clinic-system/phone/update ----

    public function test_phone_update_success()
    {
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            ['phone' => '0911111111'],
            $this->authHeaders($token)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-success', $response);
    }

    public function test_phone_update_validation_fails()
    {
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            ['phone' => 'invalid'],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-error-validation', $response);
    }

    public function test_phone_update_missing_fields()
    {
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            [],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-error-missing-fields', $response);
    }

    public function test_phone_update_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            ['phone' => '0911111111']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-error-unauthorized', $response);
    }

    public function test_phone_update_invalid_token()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            ['phone' => '0911111111'],
            $this->authHeaders('bogus-token')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-error-invalid-token', $response);
    }

    public function test_phone_update_duplicate_phone()
    {
        $existing = User::factory()->create(['phone' => '0922222223']);
        $user = User::factory()->create(['phone' => null]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/update'),
            ['phone' => '0922222223'],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PHONE_DOMAIN, 'update-error-duplicate-phone', $response);
    }

    // ---- POST /api/v1/clinic-system/phone/verify-update ----

    public function test_phone_verify_update_success()
    {
        $user = User::factory()->create(['phone' => '0944444444']);
        $token = $user->createToken('test')->plainTextToken;

        Cache::put("phone_update:{$user->id}", [
            'code'      => Hash::make('123456'),
            'new_phone' => '0999999999',
            'attempts'  => 0,
        ], now()->addMinutes(15));

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => '123456'],
            $this->authHeaders($token)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'phone' => '0999999999',
        ]);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-success', $response);
    }

    public function test_phone_verify_update_validation_fails()
    {
        $user = User::factory()->create(['phone' => '0955555555']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => ''],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-validation', $response);
    }

    public function test_phone_verify_update_missing_fields()
    {
        $user = User::factory()->create(['phone' => '0922222222']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            [],
            $this->authHeaders($token)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-missing-fields', $response);
    }

    public function test_phone_verify_update_no_pending_request()
    {
        $user = User::factory()->create(['phone' => '0911111111']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => '000000'],
            $this->authHeaders($token)
        );

        $response->assertStatus(500);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-no-request', $response);
    }

    public function test_phone_verify_update_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => '123456']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-unauthorized', $response);
    }

    public function test_phone_verify_update_invalid_token()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => '123456'],
            $this->authHeaders('bogus-token')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-invalid-token', $response);
    }

    public function test_phone_verify_update_wrong_code()
    {
        $user = User::factory()->create(['phone' => '0933333333']);
        $token = $user->createToken('test')->plainTextToken;

        Cache::put("phone_update:{$user->id}", [
            'code'      => Hash::make('123456'),
            'new_phone' => '0999999999',
            'attempts'  => 0,
        ], now()->addMinutes(15));

        $response = $this->postJson(
            $this->v1uri('/clinic-system/phone/verify-update'),
            ['code' => '999999'],
            $this->authHeaders($token)
        );

        $response->assertStatus(500);
        $this->saveFixture(self::PHONE_DOMAIN, 'verify-update-error-wrong-code', $response);
    }

    // ====================================================================
    // DEVICES DOMAIN
    // ====================================================================

    // ---- POST /api/v1/clinic-system/devices/register-token ----

    public function test_register_token_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token-12345'],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-response', $response);
    }

    public function test_register_token_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => ''],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-validation', $response);
    }

    public function test_register_token_missing_fields()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-missing-fields', $response);
    }

    public function test_register_token_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-unauthorized', $response);
    }

    public function test_register_token_invalid_token()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token'],
            $this->authHeaders('bogus-token')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-invalid-token', $response);
    }

    public function test_register_token_duplicate()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'duplicate-fcm-token'],
            $this->authHeaders($this->patientToken)
        );

        $response2 = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'duplicate-fcm-token'],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response2->assertStatus(200);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-duplicate', $response2);
    }

    public function test_register_token_multiple_users()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'shared-fcm-token'],
            $this->authHeaders($this->patientToken)
        );

        $doctorToken = $this->doctorUser->createToken('test')->plainTextToken;
        $response2 = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'shared-fcm-token'],
            $this->authHeaders($doctorToken)
        );

        $response->assertStatus(200);
        $response2->assertStatus(200);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-multiple-users', $response2);
    }
}
