<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ResourceSecurityHelper
{
    public static function gateField(string $field, mixed $value, $requester, ?int $ownerUserId): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! $requester) {
            return null;
        }

        $isOwner = $requester->id === $ownerUserId;
        $role = $requester->getRoleNames()->first();

        $doctorOrAbove = in_array($role, ['doctor', 'secretary', 'admin', 'owner']);

        if ($isOwner || $doctorOrAbove) {
            return $value;
        }

        return null;
    }

    public static function maskEmail(?string $email, $requester, ?int $ownerUserId, ?bool $reveal = null): ?string
    {
        if ($email === null) {
            return null;
        }

        if (! $requester) {
            return null;
        }

        $isOwner = $requester->id === $ownerUserId;
        $role = $requester->getRoleNames()->first();

        if ($reveal ?? ($isOwner || in_array($role, ['doctor', 'secretary', 'admin', 'owner']))) {
            return $email;
        }

        $parts = explode('@', $email);
        $name = $parts[0] ?? '';
        $domain = $parts[1] ?? '';

        return Str::mask($name, '*', 1, max(0, strlen($name) - 2)).'@'.$domain;
    }

    public static function maskPhone(?string $phone, $requester, ?int $ownerUserId, ?bool $reveal = null): ?string
    {
        if ($phone === null) {
            return null;
        }

        if (! $requester) {
            return null;
        }

        $isOwner = $requester->id === $ownerUserId;
        $role = $requester->getRoleNames()->first();

        if ($reveal ?? ($isOwner || in_array($role, ['doctor', 'secretary', 'admin', 'owner']))) {
            return $phone;
        }

        return Str::mask($phone, '*', 3, 4);
    }
}
