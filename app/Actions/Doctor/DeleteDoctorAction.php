<?php

namespace App\Actions\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class DeleteDoctorAction
{
    public function execute(Doctor $doctor): void
    {
        $hasUpcomingAppointments = $doctor->appointments()
            ->where('appointment_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasUpcomingAppointments) {
            throw new \Exception('Cannot remove the doctor. There are active upcoming appointments scheduled for this doctor.', 400);
        }

        DB::beginTransaction();
        try {
            $doctor->user->removeRole('doctor');
            $doctor->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
