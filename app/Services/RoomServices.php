<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Room;
use App\Models\Secretary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoomServices
{
    /**
     * Create a new room.
     *
     * @param array $data Room data
     * @return Room
     */
    public function createRoom(array $data): Room
    {
        return Room::create($data);
    }

    /**
     * Delete a room by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteRoom(int $id): bool
    {
        return (bool) Room::where('id', $id)->delete();
    }

    /**
     * Update a room by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRoom(int $id, array $data): bool
    {
        return (bool) Room::where('id', $id)->update($data);
    }

    /**
     * Get all rooms for a clinic with eager-loaded relationships.
     * Eager loads doctors and secretaries with their user information to prevent N+1 queries.
     *
     * @param int $clinicId
     * @return Collection
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
     *
     * @param int $id
     * @return Room|null
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
     * @param int $roomId
     * @param int $doctorId
     * @return Doctor|null
     */
    public function addDoctorToRoom(int $roomId, int $doctorId): ?Doctor
    {
        return DB::transaction(function () use ($roomId, $doctorId) {
            $room = Room::find($roomId);
            $doctor = Doctor::find($doctorId);

            if (!$room || !$doctor || $room->clinic_id !== $doctor->clinic_id) {
                return null;
            }

            $doctor->room_id = $roomId;
            $doctor->save();

            return $doctor->fresh();
        }, attempts: 3);
    }

    /**
     * Remove a doctor from a room.
     *
     * @param int $roomId
     * @param int $doctorId
     * @return bool
     */
    public function delDoctorFromRoom(int $roomId, int $doctorId): bool
    {
        $doctor = Doctor::query()
            ->where('id', $doctorId)
            ->where('room_id', $roomId)
            ->first();

        if (!$doctor) {
            return false;
        }

        $doctor->room_id = null;
        return $doctor->save();
    }

    /**
     * Assign a secretary to a room.
     * Validates clinic ownership before assignment.
     *
     * @param int $roomId
     * @param int $secretaryId
     * @return Secretary|null
     */
    public function addSecretaryToRoom(int $roomId, int $secretaryId): ?Secretary
    {
        return DB::transaction(function () use ($roomId, $secretaryId) {
            $room = Room::find($roomId);
            $secretary = Secretary::find($secretaryId);

            if (!$room || !$secretary || $room->clinic_id !== $secretary->clinic_id) {
                return null;
            }

            // attach to pivot
            $secretary->rooms()->syncWithoutDetaching([$roomId]);

            return $secretary->fresh(['rooms']);
        }, attempts: 3);
    }

    /**
     * Remove a secretary from a room.
     *
     * @param int $roomId
     * @param int $secretaryId
     * @return bool
     */
    public function delSecretaryFromRoom(int $roomId, int $secretaryId): bool
    {
        $secretary = Secretary::query()->find($secretaryId);

        if (!$secretary) {
            return false;
        }

        $secretary->rooms()->detach($roomId);
        return true;
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
