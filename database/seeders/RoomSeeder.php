<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $config = require __DIR__ . '/../data/rooms.php';
        $clinic = Clinic::first();
        if (!$clinic) return;

        foreach (range(1, $config['count']) as $i) {
            Room::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => $config['prefix'] . $i],
                []
            );
        }
    }
}
