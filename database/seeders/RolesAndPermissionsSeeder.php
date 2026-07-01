<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'admin',
        'manage patients',
        'view patients',
        'manage appointments',
        'view appointments',
        'manage schedules',
        'view schedules',
        'manage overrides',
        'view overrides',
        'access records',
        'manage finances',
        'view finances',
        'manage m/d',
    ];

    private const ROLES = [
        'owner' => [
            'admin',
            'manage patients',
            'view patients',
            'manage appointments',
            'view appointments',
            'manage schedules',
            'view schedules',
            'manage overrides',
            'view overrides',
            'access records',
            'manage finances',
            'view finances',
            'manage m/d',
        ],
        'doctor' => [
            'view patients',
            'manage appointments',
            'view appointments',
            'manage schedules',
            'view schedules',
            'manage overrides',
            'view overrides',
            'access records',
            'manage finances',
            'manage m/d',
        ],
        'secretary' => [
            'manage patients',
            'view patients',
            'manage overrides',
            'view overrides',
            'manage appointments',
            'view appointments',
            'view schedules',
            'manage finances',
            'manage m/d',
        ],
        'patient' => [
            'view appointments',
            'view finances',
            'access records',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (self::ROLES as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName])
                ->syncPermissions($permissions);
        }
    }
}
