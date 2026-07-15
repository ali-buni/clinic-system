<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Room;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;

class SecretarySeeder extends Seeder
{
    public function run(): void
    {
        $secretaryData = require __DIR__ . '/../data/secretaries.php';
        $clinic = Clinic::first();
        if (!$clinic) return;

        $rooms = Room::where('clinic_id', $clinic->id)->get();

        foreach ($secretaryData as $i => $data) {
            $user = User::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'email' => "secretary{$i}@clinic.test",
                    'fname' => $data['fname'],
                    'lname' => $data['lname'],
                    'gender' => 'female',
                    'password' => bcrypt('password'),
                ]
            );
            $user->assignRole('secretary');

            $secretary = Secretary::firstOrCreate(
                ['user_id' => $user->id, 'clinic_id' => $clinic->id],
                []
            );

            $secretary->rooms()->sync(
                $rooms->random(mt_rand(1, 3))->pluck('id')
            );
        }
    }
}
