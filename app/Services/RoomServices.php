<?php

namespace App\Services;

use App\Helpers\PermissionHelper;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Secretary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomServices
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * Create a new room.
     *
     * @param  array  $data  Room data
     */
    public function createRoom(array $data): Room
    {
        $room = Room::create($data);

        $this->activityLog->log(
            'room', 'created room', $room, null, [
                'clinic_id' => $data['clinic_id'] ?? null,
            ], 'created'
        );

        return $room;
    }

    /**
     * Delete a room by ID.
     */
    public function deleteRoom(int $id): bool
    {
        $result = (bool) Room::where('id', $id)->delete();

        $this->activityLog->log(
            'room', 'deleted room', null, null, ['room_id' => $id], 'deleted'
        );

        return $result;
    }

    /**
     * Update a room by ID.
     */
    public function updateRoom(int $id, array $data): bool
    {
        $room = Room::find($id);
        $result = $room ? (bool) $room->update($data) : false;

        if ($room) {
            $this->activityLog->log(
                'room', 'updated room', $room, null, [
                    'updated_fields' => array_keys($data),
                ], 'updated'
            );
        }

        return $result;
    }

    /**
     * Get all rooms for a clinic with eager-loaded relationships.
     * Eager loads doctors and secretaries with their user information to prevent N+1 queries.
     */
    public function getRooms(int $clinicId): Collection
    {
        return Room::query()
            ->where('clinic_id', $clinicId)
            ->with(['doctors.user', 'secretaries.user'])
            ->get();
    }

    /**
     * Get a single room by ID with eager-loaded relationships.
     */
    public function getRoomById(int $id): ?Room
    {
        return Room::query()
            ->with(['doctors.user', 'secretaries.user'])
            ->find($id);
    }

    /**
     * Assign a doctor to a room.
     * Validates clinic ownership before assignment.
     *
     * @return Doctor|null
     */
    public function addDoctorToRoom(int $roomId, int $doctorId): bool
    {
        return DB::transaction(function () use ($roomId, $doctorId) {
            $room = Room::find($roomId);
            $doctor = Doctor::find($doctorId);

            if (! $room || ! $doctor || $room->clinic_id !== $doctor->clinic_id) {
                Log::channel('structured')->warning('addDoctorToRoom failed - clinic mismatch', [
                    'room_id' => $roomId, 'doctor_id' => $doctorId,
                ]);

                return null;
            }

            $doctor->room_id = $roomId;
            $doctor->save();

            PermissionHelper::grantRoomPermission($doctor->user, $roomId);

            $this->activityLog->log(
                'room', 'assigned doctor to room', $doctor, null, ['room_id' => $roomId], 'updated'
            );

            return true;
        }, attempts: 3);
    }

    /**
     * Remove a doctor from a room.
     */
    public function delDoctorFromRoom(int $roomId, int $doctorId): bool
    {
        return DB::transaction(function () use ($roomId, $doctorId) {
            $doctor = Doctor::query()
                ->where('id', $doctorId)
                ->where('room_id', $roomId)
                ->first();

            if (! $doctor) {
                Log::channel('structured')->warning('delDoctorFromRoom - doctor not in room', [
                    'doctor_id' => $doctorId, 'room_id' => $roomId,
                ]);

                return false;
            }

            $doctor->room_id = null;
            $doctor->save();

            PermissionHelper::revokeRoomPermission($doctor->user, $roomId);

            $this->activityLog->log(
                'room', 'removed doctor from room', $doctor, null, ['room_id' => $roomId], 'updated'
            );

            return true;
        }, attempts: 3);
    }

    /**
     * Assign a secretary to multiple rooms.
     * Validates clinic ownership before assignment.
     */
    public function addSecretaryToRoom(array $roomIds, int $secretaryId): bool
    {
        return DB::transaction(function () use ($roomIds, $secretaryId) {
            $secretary = Secretary::find($secretaryId);

            if (! $secretary) {
                Log::channel('structured')->warning('addSecretaryToRoom - secretary not found', [
                    'secretary_id' => $secretaryId,
                ]);

                return false;
            }

            $validRooms = Room::whereIn('id', $roomIds)
                ->where('clinic_id', $secretary->clinic_id)
                ->pluck('id')
                ->toArray();

            if (empty($validRooms)) {
                Log::channel('structured')->warning('addSecretaryToRoom - no valid rooms', [
                    'secretary_id' => $secretaryId, 'requested_rooms' => $roomIds,
                ]);

                return false;
            }

            $secretary->rooms()->syncWithoutDetaching($validRooms);

            foreach ($validRooms as $roomId) {
                PermissionHelper::grantRoomPermission($secretary->user, $roomId);
            }

            $this->activityLog->log(
                'room', 'assigned secretary to rooms', $secretary, null, ['room_ids' => $validRooms], 'updated'
            );

            return true;
        }, attempts: 3);
    }

    /**
     * Remove a secretary from multiple rooms.
     * Validates clinic ownership before removal.
     */
    public function delSecretaryFromRoom(array $roomIds, int $secretaryId): bool
    {
        return DB::transaction(function () use ($roomIds, $secretaryId) {
            $secretary = Secretary::find($secretaryId);

            if (! $secretary) {
                Log::channel('structured')->warning('delSecretaryFromRoom - secretary not found', [
                    'secretary_id' => $secretaryId,
                ]);

                return false;
            }

            $validRooms = Room::whereIn('id', $roomIds)
                ->where('clinic_id', $secretary->clinic_id)
                ->pluck('id')
                ->toArray();

            if (empty($validRooms)) {
                Log::channel('structured')->warning('delSecretaryFromRoom - no valid rooms', [
                    'secretary_id' => $secretaryId, 'requested_rooms' => $roomIds,
                ]);

                return false;
            }

            $secretary->rooms()->detach($validRooms);

            foreach ($validRooms as $roomId) {
                PermissionHelper::revokeRoomPermission($secretary->user, $roomId);
            }

            $this->activityLog->log(
                'room', 'removed secretary from rooms', $secretary, null, ['room_ids' => $validRooms], 'updated'
            );

            return true;
        }, attempts: 3);
    }

    public function usersRooms(int $userId): Collection
    {
        return Room::query()
            ->whereHas('doctors', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orWhereHas('secretaries', function ($query) use ($userId) {
                $query->whereHas('user', function ($q) use ($userId) {
                    $q->where('id', $userId);
                });
            })
            ->with(['doctors.user', 'secretaries.user'])
            ->get();
    }
}
