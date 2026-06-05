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
            ->with(['user', 'room'])
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

            // Handle room reassignment with permission updates
            if (isset($data['room_id'])) {
                $this->handleRoomChange($secretary, $data['room_id']);
            }

            // Update user profile information if provided
            if ($secretary->user) {
                $this->updateUserProfile($secretary->user, $data);
            }

            // Update secretary room_id
            if (isset($data['room_id'])) {
                $secretary->room_id = $data['room_id'];
            }

            $secretary->save();
            return $secretary;
        }, attempts: 3);
    }

    /**
     * Handle room change with permission updates.
     *
     * @param Secretary $secretary
     * @param int $newRoomId
     * @return void
     */
    private function handleRoomChange(Secretary $secretary, int $newRoomId): void
    {
        // Validate new room exists
        if (!Room::where('id', $newRoomId)->exists()) {
            throw new \InvalidArgumentException("Room {$newRoomId} does not exist");
        }

        $oldRoomId = $secretary->room_id;
        $user = $secretary->user;

        if (!$user) {
            return;
        }

        // Grant new room permission
        PermissionHelper::grantRoomPermission($user, $newRoomId);

        // Revoke old room permission if it exists
        if ($oldRoomId) {
            PermissionHelper::revokeRoomPermission($user, $oldRoomId);
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
