<?php

namespace App\Services;

use App\Helpers\PermissionHelper;
use App\Models\Room;
use App\Models\Secretary;
use Illuminate\Support\Facades\DB;

class SecretaryService
{
    /**
     * Get secretary information with eager-loaded relationships.
     */
    public function info($id): ?Secretary
    {
        return Secretary::query()
            ->with(['user', 'rooms'])
            ->find($id);
    }

    /**
     * Update a secretary's information.
     * Handles room reassignment and updates user permissions accordingly.
     * Ensures atomicity with database transactions.
     *
     * @param int $id
     * @param array $data Secretary and user data
     * @return Secretary|null
     */
    public function update($id, array $data): ?Secretary
    {
        return DB::transaction(function () use ($id, $data) {
            $secretary = Secretary::with('user')->find($id);

            if (!$secretary) {
                return null;
            }

            // Handle rooms reassignment with permission updates
            if (isset($data['room_ids'])) {
                $this->handleRoomsChange($secretary, $data['room_ids']);
            }

            // Update user profile information if provided
            if ($secretary->user) {
                $this->updateUserProfile($secretary->user, $data);
            }

            // persist changes to secretary base fields
            $secretary->save();
            return $secretary;
        }, attempts: 3);
    }

    /**
     * Handle room change with permission updates.
     *
     * @param Secretary $secretary
     * @param array $newRoomIds
     * @return void
     */
    private function handleRoomsChange(Secretary $secretary, array $newRoomIds): void
    {
        $newRoomIds = array_values(array_filter(array_map('intval', $newRoomIds)));

        // Validate rooms exist
        $existingRoomIds = Room::whereIn('id', $newRoomIds)->pluck('id')->toArray();
        if (count($existingRoomIds) < count($newRoomIds)) {
            throw new \InvalidArgumentException('One or more room_ids are invalid');
        }

        $user = $secretary->user;
        if (!$user) {
            return;
        }

        $currentRoomIds = $secretary->rooms()->pluck('rooms.id')->toArray();

        $toAttach = array_values(array_diff($newRoomIds, $currentRoomIds));
        $toDetach = array_values(array_diff($currentRoomIds, $newRoomIds));

        if (!empty($toAttach)) {
            $secretary->rooms()->attach($toAttach);
            foreach ($toAttach as $roomId) {
                PermissionHelper::grantRoomPermission($user, $roomId);
            }
        }

        if (!empty($toDetach)) {
            $secretary->rooms()->detach($toDetach);
            foreach ($toDetach as $roomId) {
                PermissionHelper::revokeRoomPermission($user, $roomId);
            }
        }
    }

    /**
     * Update user profile fields from secretary data.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return void
     */
    private function updateUserProfile($user, array $data): void
    {
        $userFields = ['fname', 'lname', 'phone', 'dob', 'gender'];

        foreach ($userFields as $field) {
            if (isset($data[$field])) {
                $user->$field = $data[$field];
            }
        }

        $user->save();
    }
}
