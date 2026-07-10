<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    protected $locService;

    public function __construct(LocationService $locService)
    {
        $this->locService = $locService;
    }

    public function countries()
    {
        return response()->json($this->locService->getCountries());
    }

    public function governorates(string $countryCode)
    {
        return response()->json($this->locService->getGovernorates($countryCode));
    }

    public function cities(string $countryCode, string $governorateCode)
    {
        return response()->json($this->locService->getCities($countryCode, $governorateCode));
    }
}
