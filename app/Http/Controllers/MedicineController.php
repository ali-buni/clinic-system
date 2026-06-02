<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\MedicineApiService;
use App\Actions\Medicine\GetOrCreateMedicineAction;
use App\Http\Resources\MedicineResource;
use App\Http\Requests\StoreMedicineRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ModelFilter;

class MedicineController extends Controller
{
    public function search(Request $request, MedicineApiService $apiService): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:2']);

        $results = $apiService->searchMedicines($request->query('query'));

        return response()->json([
            'data'=> $results
            ], 200);
    }


    public function store(StoreMedicineRequest $request, GetOrCreateMedicineAction $action): JsonResponse
    {
        try {
            $medicine = $action->execute($request->validated());

            return response()->json([
                'message'=> 'Medicine processed successfully.',
                'medicine'=> new MedicineResource($medicine)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message'=> 'Failed to process medicine.',
                'error'=> config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $paginatedMedicines = ModelFilter::filter(new Medicine(), $request->all());

        return response()->json([
            'medicines' => MedicineResource::collection($paginatedMedicines->items()),
            'meta' => [
                'current_page' => $paginatedMedicines->currentPage(),
                'last_page'    => $paginatedMedicines->lastPage(),
                'total'        => $paginatedMedicines->total(),
            ]
        ], 200);
    }
}
