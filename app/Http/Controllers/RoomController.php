<?php

namespace App\Http\Controllers;

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

    public function index($clinicId)
    {
        $user = Auth::user();
        if (!$user->hasRole('owner')) {
            return ApiResponse::permissionDenied('You do not have access to this clinic rooms.');
        }

        return ApiResponse::success($this->roomServices->getRooms($clinicId)->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
            ];
        }));
    }

    public function indexWithInfo($clinicId)
    {
        $user = Auth::user();
        if (!$user->hasRole('owner')) {
            return ApiResponse::permissionDenied('You do not have access to this clinic rooms.');
        }

        return ApiResponse::success(RoomResource::collection($this->roomServices->getRooms($clinicId)));
    }

    public function get($roomId)
    {
        $user = Auth::user();
        if (!$user->hasRole('owner') && !$user->can("view room {$roomId}")) {
            return ApiResponse::permissionDenied('You do not have access to this room.');
        }

        return ApiResponse::success(new RoomResource($this->roomServices->getRoomById($roomId)));
    }

    public function create(RoomRequest $request)
    {
        $validated = $request->validated();

        $clinic = Clinic::query()
            ->where('id', $validated['clinic_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$clinic) {
            return ApiResponse::permissionDenied('Clinic not found or access denied.');
        }

        $room = $this->roomServices->createRoom($validated);

        return ApiResponse::success(new RoomResource($room), 'Room created successfully.', 201);
    }

    public function update(RoomRequest $request, $roomId)
    {
        $user = Auth::user();
        if (!$user->hasRole('owner')) {
            return ApiResponse::permissionDenied('You do not have access to update this room.');
        }

        $validated = $request->validated();
        $updated = $this->roomServices->updateRoom($roomId, $validated);

        if (!$updated) {
            return ApiResponse::error('Room update failed.', 422);
        }
        return ApiResponse::success(null, 'Room updated successfully.');
    }

    public function destroy($roomId)
    {
        $user = Auth::user();
        if (!$user->hasRole('owner')) {
            return ApiResponse::permissionDenied('You do not have access to delete this room.');
        }

        $deleted = $this->roomServices->deleteRoom($roomId);

        if (!$deleted) {
            return ApiResponse::error('Room deletion failed.', 422);
        }
        return ApiResponse::success(null, 'Room removed successfully.');
    }
}
