<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    const DOMAIN = 'auth';

    // ---- Login ----

    public function test_login_success()
    {
        $response = $this->postJson($this->uri('/clinic-system/login'), [
            'login' => 'patient@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);
        $this->saveFixture(self::DOMAIN, 'login-success', $response);
    }

    public function test_login_invalid_credentials()
    {
        $response = $this->postJson($this->uri('/clinic-system/login'), [
            'login' => 'patient@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
        $this->saveFixture(self::DOMAIN, 'login-error-invalid-credentials', $response);
    }

    public function test_login_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/login'), [
            'login' => '',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'login-error-validation', $response);
    }

    // ---- Register ----

    public function test_register_success()
    {
        $response = $this->postJson($this->uri('/clinic-system/register'), [
            'fname' => 'New',
            'lname' => 'Patient',
            'email' => 'newpatient@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0911111111',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'clinic_id' => $this->clinic->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'register-success', $response);
    }

    public function test_register_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/register'), [
            'fname' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'register-error-validation', $response);
    }

    public function test_register_duplicate_email()
    {
        $response = $this->postJson($this->uri('/clinic-system/register'), [
            'fname' => 'Duplicate',
            'lname' => 'User',
            'email' => 'patient@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0922222222',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'clinic_id' => $this->clinic->id,
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'register-error-duplicate-email', $response);
    }

    // ---- Forgot Password ----

    public function test_forgot_password_success()
    {
        $response = $this->postJson($this->uri('/clinic-system/forgot-password'), [
            'email' => 'patient@test.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'forgot-password-success', $response);
    }

    public function test_forgot_password_user_not_found()
    {
        $response = $this->postJson($this->uri('/clinic-system/forgot-password'), [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'forgot-password-error-not-found', $response);
    }

    public function test_forgot_password_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/forgot-password'), [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'forgot-password-error-validation', $response);
    }

    // ---- Sign Out ----

    public function test_signout_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/signout'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'signout-success', $response);
    }

    public function test_signout_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/signout'));

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'signout-error-unauthorized', $response);
    }

    // ---- Refresh Token ----

    public function test_refresh_token_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/refresh-token'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['auth_token']]);
        $this->saveFixture(self::DOMAIN, 'refresh-token-success', $response);
    }

    public function test_refresh_token_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/refresh-token'));

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'refresh-token-error-unauthorized', $response);
    }

    // ---- Reset Password (authenticated) ----

    public function test_reset_password_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/reset-password'),
            [
                'email' => 'patient@test.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'reset-password-success', $response);
    }

    public function test_reset_password_wrong_current()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/reset-password'),
            [
                'email' => 'patient@test.com',
                'password' => 'wrongpassword',
                'password_confirmation' => 'wrongpassword',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(400);
        $this->saveFixture(self::DOMAIN, 'reset-password-error-wrong-current', $response);
    }

    // ---- Reset Password with Code ----

    public function test_reset_with_code_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/reset-password-with-code'), [
            'email' => '',
            'code' => '123',
            'password' => 'short',
            'password_confirmation' => 'not-matching',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'reset-with-code-error-validation', $response);
    }
}
