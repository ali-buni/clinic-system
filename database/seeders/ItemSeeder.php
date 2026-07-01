<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $itemNames = require __DIR__ . '/../data/items.php';
        foreach ($itemNames as $name) {
            Item::firstOrCreate(['item_name' => $name]);
        }
    }
}
