<?php

namespace Database\Seeders;

use App\Models\Payment_method;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = require __DIR__ . '/../data/payment_methods.php';
        foreach ($methods as $data) {
            Payment_method::firstOrCreate(['en_name' => $data['en_name']], $data);
        }
    }
}
