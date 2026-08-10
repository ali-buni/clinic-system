<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email_hash', User::hashEmail('admin@clinic.com'))->first()
            ?? User::create(
                [
                    'email' => 'admin@clinic.com',
                    'fname' => 'System',
                    'lname' => 'Admin',
                    'phone' => '0000000000',
                    'password' => Hash::make('password'),
                    'gender' => 'male',
                ]
            );

        $admin->assignRole('admin');

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║  Admin Panel Credentials                             ');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  URL:      /admin/login                              ');
        $this->command->info('║  Email:    admin@clinic.com                          ');
        $this->command->info('║  Password: password                                  ');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
