<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'room_id' => null,
            'bio' => fake()->paragraph(),
            'appointment_duration' => fake()->randomElement([20, 30, 40, 45]),
            'consultation_fee' => fake()->randomFloat(2, 120, 300),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Doctor $doctor) {
            $room = $doctor->clinic->rooms()->inRandomOrder()->first();
            if ($room) {
                $doctor->update(['room_id' => $room->id]);
            }

            $specialtyIds = Specialty::inRandomOrder()->take(rand(1, 2))->pluck('id');
            if ($specialtyIds->isNotEmpty()) {
                $syncData = [];
                foreach ($specialtyIds as $i => $id) {
                    $syncData[$id] = ['is_primary' => $i === 0];
                }
                $doctor->specialties()->sync($syncData);
            }
        });
    }
}
