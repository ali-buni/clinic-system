<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Clinic;
use App\Models\Room;
use App\Services\ApiResponse;
use App\Services\RoomServices;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    protected RoomServices $roomServices;

    public function __construct(RoomServices $roomServices)
    {
        $this->roomServices = $roomServices;
    }

    /**
     * Get all rooms for a clinic (basic list).
     */
    public function index($clinicId)
    {
        $auth = $this->authorizeRole('owner', 'You do not have access to this clinic rooms.');
        if ($auth !== true) {
            return $auth;
        }

        return ApiResponse::success($this->roomServices->getRooms($clinicId)->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
            ];
        }));
    }

    /**
     * Get all rooms for a clinic with detailed information.
     */
    public function indexWithInfo($clinicId)
    {
        $auth = $this->authorizeRole('owner', 'You do not have access to this clinic rooms.');
        if ($auth !== true) {
            return $auth;
        }

        return ApiResponse::success(RoomResource::collection($this->roomServices->getRooms($clinicId)));
    }

    /**
     * Get a single room with detailed information.
     */
    public function get($roomId)
    {
        $user = Auth::user();

        // Owner can view any room, others need specific permission
        if (!$this->isOwner() && !$user->can(PermissionHelper::viewRoom($roomId))) {
            return ApiResponse::permissionDenied('You do not have access to this room.');
        }

        $room = $this->roomServices->getRoomById($roomId);

        if (!$room) {
            return ApiResponse::error('Room not found', 404);
        }

        return ApiResponse::success(new RoomResource($room));
    }

    /**
     * Create a new room.
     */
    public function create(RoomRequest $request)
    {
        $validated = $request->validated();

        // Verify user owns this clinic
        $clinic = Clinic::where('id', $validated['clinic_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$clinic) {
            return ApiResponse::permissionDenied('Clinic not found or access denied.');
        }

        $room = $this->roomServices->createRoom($validated);

        return ApiResponse::success(null, 'Room created successfully.', 201);
    }

    /**
     * Update a room.
     */
    public function update(RoomRequest $request, $roomId)
    {
        $auth = $this->authorizeRole('owner', 'You do not have access to update this room.');
        if ($auth !== true) {
            return $auth;
        }

        $validated = $request->validated();
        $updated = $this->roomServices->updateRoom($roomId, $validated);

        if (!$updated) {
            return ApiResponse::error('Room update failed.', 422);
        }
        return ApiResponse::success(null, 'Room updated successfully.');
    }

    /**
     * Delete a room.
     */
    public function destroy($roomId)
    {
        $auth = $this->authorizeRole('owner', 'You do not have access to delete this room.');
        if ($auth !== true) {
            return $auth;
        }

        $deleted = $this->roomServices->deleteRoom($roomId);

        if (!$deleted) {
            return ApiResponse::error('Room deletion failed.', 422);
        }
        return ApiResponse::success(null, 'Room removed successfully.');
    }
}
