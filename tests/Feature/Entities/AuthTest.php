<?php

namespace Tests\Feature\Entities;

use App\Models\Verification_code;
use Illuminate\Support\Facades\Hash;

class AuthTest extends BaseEntityTestCase
{
    protected string $entityName = 'auth';

    public function test_login_success()
    {
        $endpoint = $this->v1uri('/clinic-system/login');
        $payload = ['login' => $this->ownerUser->email, 'password' => 'password'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'login-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_login_invalid_credentials()
    {
        $endpoint = $this->v1uri('/clinic-system/login');
        $payload = ['login' => $this->ownerUser->email, 'password' => 'wrongpassword'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'login-invalid-credentials', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_login_not_found()
    {
        $endpoint = $this->v1uri('/clinic-system/login');
        $payload = ['login' => 'nonexistent@test.com', 'password' => 'password'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'login-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_login_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/login');
        $payload = ['login' => 'not-an-email', 'password' => 'short'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'login-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_login_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/login');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'login-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_register_success()
    {
        $endpoint = $this->v1uri('/clinic-system/register');
        $payload = [
            'fname' => 'New',
            'lname' => 'Patient',
            'email' => 'newpatient@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_register_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/register');
        $payload = [
            'fname' => '',
            'lname' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'short',
            'clinic_id' => 999,
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_register_duplicate_email()
    {
        $endpoint = $this->v1uri('/clinic-system/register');
        $payload = [
            'fname' => 'Another',
            'lname' => 'User',
            'email' => $this->ownerUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-duplicate-email', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_register_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/register');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_register_password_mismatch()
    {
        $endpoint = $this->v1uri('/clinic-system/register');
        $payload = [
            'fname' => 'New',
            'lname' => 'Patient',
            'email' => 'newpatient2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-password-mismatch', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_forgot_password_success()
    {
        $endpoint = $this->v1uri('/clinic-system/forgot-password');
        $payload = ['email' => $this->ownerUser->email];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'forgot-password-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_forgot_password_not_found()
    {
        $endpoint = $this->v1uri('/clinic-system/forgot-password');
        $payload = ['email' => 'nonexistent@test.com'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'forgot-password-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_forgot_password_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/forgot-password');
        $payload = ['email' => 'not-an-email'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'forgot-password-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_forgot_password_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/forgot-password');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'forgot-password-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reset_with_code_success()
    {
        $code = '123456';
        Verification_code::create([
            'user_id' => $this->ownerUser->id,
            'type' => 'email_reset',
            'sent_to' => $this->ownerUser->email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $endpoint = $this->v1uri('/clinic-system/reset-password-with-code');
        $payload = [
            'email' => $this->ownerUser->email,
            'code' => $code,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-with-code-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_reset_with_code_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password-with-code');
        $payload = [
            'email' => 'not-an-email',
            'code' => 'abc',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-with-code-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reset_with_code_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password-with-code');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-with-code-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reset_with_code_not_found()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password-with-code');
        $payload = [
            'email' => 'nonexistent@test.com',
            'code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-with-code-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_reset_with_code_invalid_code()
    {
        $correctCode = '123456';
        Verification_code::create([
            'user_id' => $this->ownerUser->id,
            'type' => 'email_reset',
            'sent_to' => $this->ownerUser->email,
            'code_hash' => Hash::make($correctCode),
            'expires_at' => now()->addMinutes(15),
        ]);

        $endpoint = $this->v1uri('/clinic-system/reset-password-with-code');
        $payload = [
            'email' => $this->ownerUser->email,
            'code' => '654321',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-with-code-invalid-code', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_signout_success()
    {
        $endpoint = $this->v1uri('/clinic-system/signout');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'signout-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_signout_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/signout');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'signout-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_signout_invalid_token()
    {
        $endpoint = $this->v1uri('/clinic-system/signout');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'signout-invalid-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_reset_password_success()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [
            'email' => $this->ownerUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'reset-password-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_reset_password_wrong_current()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [
            'email' => $this->ownerUser->email,
            'password' => 'wrongpassword',
            'password_confirmation' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'reset-password-wrong-current', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(400);
    }

    public function test_reset_password_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'reset-password-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reset_password_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'reset-password-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reset_password_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [
            'email' => $this->ownerUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'reset-password-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_reset_password_new_password_mismatch()
    {
        $endpoint = $this->v1uri('/clinic-system/reset-password');
        $payload = [
            'email' => $this->ownerUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'reset-password-new-password-mismatch', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_refresh_token_success()
    {
        $endpoint = $this->v1uri('/clinic-system/refresh-token');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'refresh-token-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_refresh_token_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/refresh-token');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'refresh-token-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_refresh_token_invalid_token()
    {
        $endpoint = $this->v1uri('/clinic-system/refresh-token');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'refresh-token-invalid-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_refresh_token_expired_token()
    {
        $tempToken = $this->ownerUser->createToken('expired-test')->plainTextToken;
        $this->ownerUser->tokens()->delete();

        $endpoint = $this->v1uri('/clinic-system/refresh-token');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($tempToken));        $this->saveResult($this->entityName, 'refresh-token-expired-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }
}
