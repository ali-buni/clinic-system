<?php

namespace Tests\Feature\Entities;

class UserEndpointTest extends BaseEntityTestCase
{
    protected string $entityName = 'user-endpoint';

    public function test_get_authenticated_user_success()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'get-authenticated-user-success', 'GET', $endpoint, [], $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_get_authenticated_user_unauthenticated()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'get-authenticated-user-unauthenticated', 'GET', $endpoint, [], $response);
        $response->assertStatus(401);
    }

    public function test_get_authenticated_user_as_doctor()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'get-authenticated-user-as-doctor', 'GET', $endpoint, [], $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_get_authenticated_user_as_owner()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'get-authenticated-user-as-owner', 'GET', $endpoint, [], $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_get_authenticated_user_as_secretary()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint, $this->authHeaders($this->secretaryToken));        $this->saveResult($this->entityName, 'get-authenticated-user-as-secretary', 'GET', $endpoint, [], $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_get_authenticated_user_invalid_token()
    {
        $endpoint = $this->uri('/user');
        $response = $this->getJson($endpoint, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'get-authenticated-user-invalid-token', 'GET', $endpoint, [], $response);
        $response->assertStatus(401);
    }
}
