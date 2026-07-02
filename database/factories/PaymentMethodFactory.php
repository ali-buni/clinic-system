<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment_method>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(PaymentMethodType::cases());

        return [
            'ar_name'   => $type->value === 'Cash' ? 'نقداً' : $type->value,
            'en_name'   => $type->value,
            'type'      => $type,
            'is_active' => true,
        ];
    }
}
