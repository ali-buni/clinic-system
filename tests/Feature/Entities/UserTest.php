<?php

namespace Tests\Feature\Entities;

use Illuminate\Http\UploadedFile;

class UserTest extends BaseEntityTestCase
{
    protected string $entityName = 'user';

    public function test_users_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/users/info'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'info-success', 'GET', '/users/info', [], $response);
        $response->assertStatus(200);
    }

    public function test_users_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/users/info'));        $this->saveResult($this->entityName, 'info-unauthenticated', 'GET', '/users/info', [], $response);
        $response->assertStatus(401);
    }

    public function test_update_image_success()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->post(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['profile_image' => $file],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-image-success', 'POST', '/users/update-image', ['profile_image' => 'UploadedFile'], $response);
        $response->assertStatus(200);
    }

    public function test_update_image_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['profile_image' => 'not-an-image'],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-image-validation', 'POST', '/users/update-image', ['profile_image' => 'not-an-image'], $response);
        $response->assertStatus(422);
    }

    public function test_update_image_unauthenticated()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->post(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['profile_image' => $file]
        );        $this->saveResult($this->entityName, 'update-image-unauthenticated', 'POST', '/users/update-image', ['profile_image' => 'UploadedFile'], $response);
        $response->assertStatus(401);
    }

    public function test_get_image_url_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/users/image-url'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'image-url-success', 'GET', '/users/image-url', [], $response);
        $response->assertStatus(200);
    }

    public function test_get_image_url_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/users/image-url'));        $this->saveResult($this->entityName, 'image-url-unauthenticated', 'GET', '/users/image-url', [], $response);
        $response->assertStatus(401);
    }
}
