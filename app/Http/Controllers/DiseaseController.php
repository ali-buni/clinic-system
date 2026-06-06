<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Resources\DiseaseResource;
use App\Actions\Disease\GetOrCreateDiseaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DiseaseApiService;
use App\Services\ModelFilter;
use App\Services\ApiResponse;
class DiseaseController extends Controller
{
    public function search(Request $request, DiseaseApiService $apiService): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:2']);

        $results = $apiService->searchDiseases($request->query('query'));

        return ApiResponse::success($results, 'Diseases search results retrieved successfully.');
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

    public function index(Request $request): JsonResponse
    {
        $paginatedDiseases = ModelFilter::filter(new Disease(), $request->all());

        return ApiResponse::pagination($paginatedDiseases, 'Diseases collection retrieved successfully.');
    }
}
