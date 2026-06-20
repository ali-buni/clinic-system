<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolePermissions = [
            'give role',
            'restore role',
        ];

        $permissionPermissions = [
            'give permission',
            'restore permission',
        ];

        $clinicPermissions = [
            'view patients',
            'create patients',
            'edit patients',
            'delete patients',

            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',

            'view prescriptions',
            'create prescriptions',
            'edit prescriptions',
            'delete prescriptions',

            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',

            'view payments',
            'create payments',
            'edit payments',
            'delete payments',

            'view schedules',
            'edit schedules',

            'view rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',

            'view patient records',
            'create patient records',
            'edit patient records',
            'delete patient records',
        ];

        $permissions = array_unique(array_merge(
            $rolePermissions,
            $permissionPermissions,
            $clinicPermissions,
        ));

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner = Role::firstOrCreate(['name' => 'owner']);
        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $patient = Role::firstOrCreate(['name' => 'patient']);

        $owner->syncPermissions([
            'give role',
            'restore role',
            'give permission',
            'restore permission',

            'view patients',
            'view appointments',
            'view prescriptions',
            'view invoices',
            'view payments',
            'view schedules',
            'view patient records',

            'view rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',
        ]);

        $doctor->syncPermissions([
            'view patients',

            'view appointments',
            'delete appointments',

            'view prescriptions',
            'create prescriptions',
            'edit prescriptions',

            'view patient records',
            'create patient records',
            'edit patient records',

            'view schedules',
            'edit schedules',

            'view invoices',
            'create invoices',
        ]);

        $secretary->syncPermissions([
            'view patients',
            'delete patients',

            'view appointments',

            'view invoices',

            'view payments',
            'create payments',

            'view rooms',

            'view schedules',
        ]);

        $patient->syncPermissions([
            'view appointments',
            'view prescriptions',
            'view invoices',
            'view patient records',
        ]);
    }
}
