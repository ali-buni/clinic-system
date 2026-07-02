<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = require __DIR__ . '/../data/items.php';
        foreach ($items as $item) {
            Item::firstOrCreate(['item_name' => $item['item_name']]);
        }
    }
}
