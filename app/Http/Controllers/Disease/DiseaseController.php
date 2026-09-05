<?php

namespace App\Http\Controllers\Disease;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Http\Requests\Disease\StoreDiseaseRequest;
use App\Http\Resources\Disease\DiseaseResource;
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
        $query = (string) $request->query('query', '');

        $filters = $request->validated();
        $filters['search'] = $query;
        $filters['column'] = 'en_name,ar_name,code';

        $queryBuilder = Disease::query();

        $diseases = ModelFilter::filter($queryBuilder, $filters);
        $results = $apiService->searchDiseases($query);

        if ($diseases->isEmpty() && empty($results)) {
            return ApiResponse::success([], 'No diseases found.');
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
