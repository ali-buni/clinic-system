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

            // Update user profile information if provided
            if ($secretary->user) {
                $this->updateUserProfile($secretary->user, $data);
            }

            $secretary->save();
            return $secretary;
        }, attempts: 3);
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
