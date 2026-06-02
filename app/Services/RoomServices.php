<?php

namespace App\Services;

use App\Http\Resources\RoomResource;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Secretary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoomServices
{
    public function createRoom(array $data): Room
    {
        return Room::query()->create($data);
    }

    public function deleteRoom(int $id): bool
    {
        return (bool) Room::query()->where('id', $id)->delete();
    }

    public function updateRoom(int $id, array $data): bool
    {
        return (bool) Room::query()
            ->where('id', $id)
            ->update($data);
    }

    public function getRooms(int $clinicId): Collection
    {
        return Room::query()
            ->where('clinic_id', $clinicId)
            ->with(['doctors.user', 'secretaries.user'])
            ->get();
    }

    public function getRoomById(int $id): ?Room
    {
        return Room::query()
            ->with(['doctors.user', 'secretaries.user'])
            ->find($id);
    }

    public function addDoctorToRoom(int $roomId, int $doctorId): ?Doctor
    {
        return DB::transaction(function () use ($roomId, $doctorId) {
            $room = Room::query()->find($roomId);
            $doctor = Doctor::query()->find($doctorId);

            if (!$room || !$doctor || $room->clinic_id !== $doctor->clinic_id) {
                return null;
            }

            $doctor->room_id = $roomId;
            $doctor->save();

            return $doctor->fresh();
        });
    }

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

    public function addSecretaryToRoom(int $roomId, int $secretaryId): ?Secretary
    {
        return DB::transaction(function () use ($roomId, $secretaryId) {
            $room = Room::query()->find($roomId);
            $secretary = Secretary::query()->find($secretaryId);

            if (!$room || !$secretary || $room->clinic_id !== $secretary->clinic_id) {
                return null;
            }

            $secretary->room_id = $roomId;
            $secretary->save();

            return $secretary->fresh();
        });
    }

    public function delSecretaryFromRoom(int $roomId, int $secretaryId): bool
    {
        $secretary = Secretary::query()
            ->where('id', $secretaryId)
            ->where('room_id', $roomId)
            ->first();

        if (!$secretary) {
            return false;
        }

        $secretary->room_id = null;

        return $secretary->save();
    }
}
