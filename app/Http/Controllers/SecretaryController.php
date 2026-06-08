<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecretaryRequest;
use App\Http\Resources\SecretaryResource;
use App\Models\Room;
use App\Services\ApiResponse;
use App\Services\SecretaryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class SecretaryController extends Controller
{
    public function __construct(private SecretaryService $secretary_service) {}

    public function info(Request $request, $id)
    {
        $user = Auth::user();

        // Get user's room ID (if user is assigned to a room)
        $user_room_id = $user->doctorProfile?->room_id
            ?? ($user->secretaryProfile?->rooms()->pluck('rooms.id')->first() ?? null)
            ?? null;

        $secretary = $this->secretary_service->info($id);

        if (!$secretary) {
            return ApiResponse::error('Secretary not found', 404);
        }
        // Authorize using policy
        try {
            $secretaryRoomIds = $secretary->rooms->pluck('id')->toArray();
            Gate::authorize('viewSecretary', [$user, $user_room_id, $secretaryRoomIds]);
            return ApiResponse::success(new SecretaryResource($secretary));
        } catch (AuthorizationException $e) {
            return ApiResponse::error('Unauthorized: ' . $e->getMessage(), 403);
        }
    }

    public function update(SecretaryRequest $request, $id)
    {
        $data = $request->validated();
        $secretary = $this->secretary_service->update($id, $data);

        if (!$secretary) {
            return ApiResponse::error('Secretary not found', 404);
        }
        return ApiResponse::success();
    }
}
