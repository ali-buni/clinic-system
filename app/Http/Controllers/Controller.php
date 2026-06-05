<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Check if the authenticated user has a specific role.
     * Returns true if authorized, or ApiResponse error if not.
     *
     * @param string $role Role to check
     * @param string $message Error message if unauthorized
     * @return mixed true if authorized, otherwise ApiResponse error
     */
    protected function authorizeRole(string $role, string $message = 'Unauthorized')
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole($role)) {
            return \App\Services\ApiResponse::permissionDenied($message);
        }

        return true;
    }

    /**
     * Check if the authenticated user is an owner.
     *
     * @return bool
     */
    protected function isOwner(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('owner');
    }

    /**
     * Check if the authenticated user is an doctor.
     *
     * @return bool
     */
    protected function isDoctor(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('doctor');
    }

    /**
     * Check if the authenticated user is an secretary.
     *
     * @return bool
     */
    protected function isSecretary(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('secretary');
    }

    /**
     * Check if the authenticated user has a specific permission.
     * Returns true if authorized, or ApiResponse error if not.
     *
     * @param string $permission Permission to check
     * @param string $message Error message if unauthorized
     * @return mixed true if authorized, otherwise ApiResponse error
     */
    protected function authorizePermission(string $permission, string $message = 'Unauthorized')
    {
        $user = Auth::user();

        if (!$user || !$user->can($permission)) {
            return \App\Services\ApiResponse::permissionDenied($message);
        }

        return true;
    }
}
