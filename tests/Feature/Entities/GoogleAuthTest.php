<?php

namespace Tests\Feature\Entities;

class GoogleAuthTest extends BaseEntityTestCase
{
    protected string $entityName = 'google-auth';

    public function test_redirect_success()
    {
        $endpoint = $this->uri('/auth/google');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'redirect-success', 'GET', $endpoint, [], $response);
        $response->assertStatus(200);
    }

    public function test_callback_missing_code()
    {
        $endpoint = $this->uri('/auth/google/callback');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'callback-missing-code', 'GET', $endpoint, [], $response);
        $response->assertStatus(401);
    }

    public function test_callback_with_invalid_code()
    {
        $endpoint = $this->uri('/auth/google/callback?code=invalid_google_code');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'callback-with-invalid-code', 'GET', $endpoint, [], $response);
        $response->assertStatus(401);
    }
}
