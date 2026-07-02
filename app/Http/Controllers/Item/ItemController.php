<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemFilterRequest;
use App\Http\Requests\Item\ItemRequest;
use App\Http\Resources\Item\ItemResource;
use App\Models\Item;
use App\Services\ApiResponse;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct(protected ItemService $itemService) {}

    public function index(ItemFilterRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $paginated = $this->itemService->index($filters);

            if ($paginated->total() === 0) {
                return ApiResponse::error('Items not found', 404);
            }

            return ApiResponse::pagination(
                $paginated,
                'Items retrieved successfully.',
                ItemResource::collection($paginated->items())
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Server Error', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }

    public function store(ItemRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            if (! $user->hasRole('admin') && isset($validated['clinic_id'])) {
                $ownerClinicId = $user->clinicOwner?->id ?? $user->doctorProfile?->clinic_id;
                if ($ownerClinicId !== $validated['clinic_id']) {
                    return ApiResponse::permissionDenied('Cannot create item for this clinic.');
                }
            }

            $item = $this->itemService->create($validated);

            return ApiResponse::success(
                new ItemResource($item),
                'Item created successfully.',
                201
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Server Error', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }

    public function destroy(int $itemId): JsonResponse
    {
        try {
            $user = Auth::user();
            $isAdmin = $user->hasRole('admin');
            $ownerClinicId = $user->clinicOwner?->id;

            $item = Item::find($itemId);
            if (! $item) {
                return ApiResponse::error('Item not found', 404);
            }

            if (! $this->itemService->delete($item, $ownerClinicId, $isAdmin)) {
                return ApiResponse::permissionDenied('Not authorized to delete this item.');
            }

            return ApiResponse::success(null, 'Item deleted successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error('Server Error', 500, config('app.debug') ? ['error' => $e->getMessage()] : null);
        }
    }
}
