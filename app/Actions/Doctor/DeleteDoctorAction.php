<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class DeleteDoctorAction
{
    public function execute(Doctor $doctor): void
    {
        $hasUpcomingAppointments = $doctor->appointments()
            ->where('start_time', '>=', now())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->exists();

        if ($hasUpcomingAppointments) {
            throw new \Exception(
                'Cannot remove the doctor. There are active upcoming appointments scheduled for this doctor.',
                400
            );
        }

        DB::transaction(function () use ($doctor) {

            if ($doctor->user) {
                $doctor->user->removeRole('doctor');
            }

            $doctor->delete();

            $doctor->update(['room_id' => null]);
        });
    }
}
