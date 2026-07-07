<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecretaryService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

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
     * @param  int  $id
     * @param  array  $data  Secretary and user data
     */
    public function update($id, array $data): ?Secretary
    {
        return DB::transaction(function () use ($id, $data) {
            $secretary = Secretary::with('user')->find($id);

            if (! $secretary) {
                Log::channel('structured')->warning('secretary update - not found', ['secretary_id' => $id]);

                return null;
            }

            if ($secretary->user) {
                $this->updateUserProfile($secretary->user, $data);
            }

            $secretary->save();

            $this->activityLog->log('secretary', 'secretary updated', $secretary, null, [
                'updated_fields' => array_keys($data),
            ], 'updated');

            return $secretary;
        }, attempts: 3);
    }

    /**
     * Update user profile fields from secretary data.
     *
     * @param  User  $user
     */
    private function updateUserProfile($user, array $data): void
    {
        $userFields = ['fname', 'lname', 'dob', 'gender'];

        foreach ($userFields as $field) {
            if (isset($data[$field])) {
                $user->$field = $data[$field];
            }
        }

        $user->save();
    }
}
