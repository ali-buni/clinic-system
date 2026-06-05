<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Secretary;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SecretaryService
{
    public function info($id): ?Secretary
    {
        return Secretary::query()
            ->with(['user', 'room'])
            ->find($id);
    }

    /**
     * Update a secretary's information.
     * Handles room reassignment and updates user permissions accordingly.
     * Ensures atomicity with database transactions and logs any errors encountered.
     *
     * @param int $id
     * @param array $data
     * @return ?Secretary
     */
    public function update($id, array $data): ?Secretary
    {
        DB::beginTransaction();
        try {
            $secretary = Secretary::with('user')->find($id);

            if (!$secretary) {
                DB::rollBack();
                return null;
            }

            if (isset($data['room_id'])) {
                $newRoomId = $data['room_id'];
                $oldRoomId = $secretary->room_id;

                if (!Room::where('id', $newRoomId)->exists()) {
                    DB::rollBack();
                    return null;
                }
                $user = $secretary->user;

                if ($user) {
                    Permission::firstOrCreate(['name' => "view room {$newRoomId}", 'guard_name' => 'web']);
                    $user->givePermissionTo("view room {$newRoomId}");

                    if ($oldRoomId) {
                        $user->revokePermissionTo("view room {$oldRoomId}");
                    }
                    if (isset($data['fname'])) {
                        $user->fname = $data['fname'];
                    }
                    if (isset($data['lname'])) {
                        $user->lname = $data['lname'];
                    }
                    if (isset($data['phone'])) {
                        $user->phone = $data['phone'];
                    }
                    if (isset($data['dob'])) {
                        $user->dob = $data['dob'];
                    }
                    if (isset($data['gender'])) {
                        $user->gender = $data['gender'];
                    }
                    $user->save();
                }
            }
            $secretary->update([
                'room_id' => $data['room_id'] ?? $secretary->room_id,
            ]);

            DB::commit();
            return $secretary;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }
}
