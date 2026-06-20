<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Resources\DiseaseResource;
use App\Actions\Disease\GetOrCreateDiseaseAction;
use App\Http\Requests\FilterRequest;
use Illuminate\Http\JsonResponse;
use App\Services\DiseaseApiService;
use App\Services\ModelFilter;
use App\Services\ApiResponse;

class DiseaseController extends Controller
{
    public function searchDisease(FilterRequest $request, DiseaseApiService $apiService): JsonResponse
    {
        $filters = $request->validated();
        $query = Disease::query();

        $diseases = ModelFilter::filter($query, $filters);
        $results = $apiService->searchDiseases($request->query('query'));

        if (empty($results) && empty($diseases->items())) {
            return ApiResponse::error('no diseases found');
        }
        return ApiResponse::success(array_merge($diseases->items(), $results), 'Diseases search results retrieved successfully.');
    }

    public function store(StoreDiseaseRequest $request, GetOrCreateDiseaseAction $action): JsonResponse
    {
        try {
            $disease = $action->execute($request->validated());

            return ApiResponse::success(
                new DiseaseResource($disease),
                'Disease processed successfully.',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to process disease records.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }
}
