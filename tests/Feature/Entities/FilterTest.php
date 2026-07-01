<?php

namespace Tests\Feature\Entities;

class FilterTest extends BaseEntityTestCase
{
    protected string $entityName = 'filter';

    public function test_filter_success()
    {
        $endpoint = $this->uri('/filter');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'filter-success', 'GET', $endpoint, [], $response);
        $response->assertStatus(200);
    }

    public function test_filter_with_params()
    {
        $endpoint = $this->uri('/filter?per_page=5&fname=Clinic');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'filter-with-params', 'GET', $endpoint, ['per_page' => 5, 'fname' => 'Clinic'], $response);
        $response->assertStatus(200);
    }

    public function test_filter_invalid_per_page()
    {
        $endpoint = $this->uri('/filter?per_page=-1');
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'filter-invalid-per-page', 'GET', $endpoint, ['per_page' => -1], $response);
        $response->assertStatus(200);
    }
}
