<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Invoice;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. توليد 20 مادة طبية بالسيستم أولاً
        $items = Item::factory()->count(20)->create();

        // 2. توليد 10 فواتير وربطها بالمواد عبر جدول الـ Pivot
        Invoice::factory()
            ->count(10)
            ->create()
            ->each(function ($invoice) use ($items) {
                $invoice->items()->attach(
                    $items->random(rand(1, 3))->pluck('id')->toArray(),
                    [
                        'quantity' => rand(1, 5),
                        'price'    => rand(20, 200),
                    ]
                );
            });
    }
}