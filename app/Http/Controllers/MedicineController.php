<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\MedicineApiService;
use App\Actions\Medicine\GetOrCreateMedicineAction;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\MedicineResource;
use App\Http\Requests\StoreMedicineRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ApiResponse;
use App\Services\ModelFilter;

class MedicineController extends Controller
{
    public function searchMedicine(FilterRequest $request, MedicineApiService $apiService): JsonResponse
    {
        $filters = $request->validated();
        $query = Medicine::query();

        $local = ModelFilter::filter($query, $filters);

        $apiResults = $apiService->searchMedicines($request->query('query'));

        if (empty($apiResults) && empty($local->items())) {
            return ApiResponse::error('no medicines found');
        }

        return ApiResponse::success(array_merge($local->items(), $apiResults), 'Medicines search results retrieved successfully.');
    }

    public function store(StoreMedicineRequest $request, GetOrCreateMedicineAction $action): JsonResponse
    {
        try {
            $medicine = $action->execute($request->validated());

            return ApiResponse::success(
                new MedicineResource($medicine),
                'Medicine processed successfully.',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to process medicine.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }
}
