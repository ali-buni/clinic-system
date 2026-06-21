<?php

namespace App\Helpers;

/**
 * Permission names and constants for the application.
 */
class PermissionHelper
{
    /**
     * Get the permission name for viewing a specific room.
     */
    public static function viewRoom(int $roomId): string
    {
        return "view room {$roomId}";
    }

    /**
     * Create or get room permission.
     */
    public static function ensureRoomPermission(int $roomId, string $guardName = 'web')
    {
        $permissionName = self::viewRoom($roomId);
        
        return \Spatie\Permission\Models\Permission::firstOrCreate(
            ['name' => $permissionName, 'guard_name' => $guardName]
        );
    }

    /**
     * Grant room permission to user.
     */
    public static function grantRoomPermission(\App\Models\User $user, int $roomId): void
    {
        self::ensureRoomPermission($roomId);
        $user->givePermissionTo(self::viewRoom($roomId));
    }

    /**
     * Revoke room permission from user.
     */
    public static function revokeRoomPermission(\App\Models\User $user, int $roomId): void
    {
        self::ensureRoomPermission($roomId);
        $user->revokePermissionTo(self::viewRoom($roomId));
    }
}
