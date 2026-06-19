<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretaryFactory extends Factory
{
    protected $model = Secretary::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Secretary $secretary) {
            $rooms = $secretary->clinic->rooms()->inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($rooms->isNotEmpty()) {
                $secretary->rooms()->sync($rooms);
            }
        });
    }
}
