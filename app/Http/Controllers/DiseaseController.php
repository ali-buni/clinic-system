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

class DiseaseController extends Controller
{
    public function search(Request $request, DiseaseApiService $apiService): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:2']);

        $results = $apiService->searchDiseases($request->query('query'));

        return response()->json([
            'data'=> $results
            ], 200);
    }

    public function store(StoreDiseaseRequest $request, GetOrCreateDiseaseAction $action): JsonResponse
    {
        try {
            $disease = $action->execute($request->validated());

            return response()->json([
                'message' => 'Disease processed successfully.',
                'disease' => new DiseaseResource($disease)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process disease records.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $paginatedDiseases = ModelFilter::filter(new Disease(), $request->all());

        return response()->json([
            'diseases' => DiseaseResource::collection($paginatedDiseases->items()),
            'meta' => [
                'current_page' => $paginatedDiseases->currentPage(),
                'last_page'    => $paginatedDiseases->lastPage(),
                'total'        => $paginatedDiseases->total(),
            ]
        ], 200);
    }
}
