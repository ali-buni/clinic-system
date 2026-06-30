<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerClinicSeeder extends Seeder
{
    public function run(): void
    {
        $config = require __DIR__ . '/../data/owner_clinic.php';
        $ownerData = $config['owner'];
        $owner = User::firstOrCreate(
            ['phone' => $ownerData['phone']],
            [
                'email' => $ownerData['email'],
                'fname' => $ownerData['fname'],
                'lname' => $ownerData['lname'],
                'gender' => $ownerData['gender'],
                'password' => bcrypt($ownerData['password']),
            ]
        );
        $owner->assignRole('owner');

        Clinic::firstOrCreate(
            ['user_id' => $owner->id],
            $config['clinic']
        );
    }
}
