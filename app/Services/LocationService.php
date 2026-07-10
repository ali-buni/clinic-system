<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Location;
use Illuminate\Support\Facades\Http;

class LocationService
{
    protected string $baseUrl = 'https://api.countrystatecity.in/v1/';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.csc.ApiKey');
    }

    private function makeRequest(string $endpoint): array
    {
        $response = Http::withHeaders([
            'X-CSCAPI-KEY' => $this->apiKey
        ])->get("{$this->baseUrl}/{$endpoint}");

        return $response->successful() ? $response->json() : [];
    }

    public function getCountries(): array
    {
        return $this->makeRequest('countries');
    }

    public function getGovernorates(string $countryCode): array
    {
        return $this->makeRequest("countries/{$countryCode}/states");
    }

    public function getCities(string $countryCode, string $governorateCode): array
    {
        return $this->makeRequest("countries/{$countryCode}/states/{$governorateCode}/cities");
    }

    public function store(string $name, string $city, string $governorate, string $country): array
    {
        return Location::create(compact('name', 'city', 'governorate', 'country'))->toArray();
    }
}
