<?php

namespace App\Services;

use App\Events\SendMsgEvent;
use App\Models\Appointment;
use App\Models\Patient;
use App\Traits\BookingTrait;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    use BookingTrait;

    /**
     * Book a new appointment. Throws ModelNotFoundException if related models not found.
     */

    public function cancelAppointment(int $id, string $reason)
    {
        return DB::transaction(function () use ($id, $reason) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'scheduled') {
                throw new Exception("the appointment is not scheduled");
            }
            $appointment->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason ?? "no reason",
            ]);

            // TODO: send msg to doctor and patient
            return true;
        });
    }

    public function completeAppointment(int $id)
    {
        return DB::transaction(function () use ($id) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'confirmed') {
                throw new Exception("the appointment is not confirmed");
            }
            $appointment->update(['status' => 'completed']);

            // TODO: send msg to secretary
            return true;
        });
    }

    public function markConfirmed(int $id)
    {
        return DB::transaction(function () use ($id) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'scheduled') {
                throw new Exception("the appointment is not scheduled");
            }
            $appointment->update(['status' => 'confirmed']);

            // TODO: send msg
            return true;
        });
    }

    public function getAppointment(int $id): Appointment
    {
        return Appointment::with(['doctor', 'patient', 'room', 'type'])->findOrFail($id);
    }

    public function getPatientAppointments(int $patientId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('patient_id', $patientId)->with(['doctor', 'patient', 'room', 'type']);
        return ModelFilter::filter($q, $data);
    }

    public function getDoctorAppointments(int $doctorId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('doctor_id', $doctorId)->with(['patient', 'clinic', 'room', 'type']);
        return ModelFilter::filter($q, $data);
    }

    public function getClinicAppointments(int $clinicId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('clinic_id', $clinicId)
            ->with(['doctor', 'patient', 'room', 'type']);
        return ModelFilter::filter($q, $data);
    }

    public function getRoomAppointments(int $roomId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('room_id', $roomId)->with(['doctor', 'patient', 'room', 'type']);
        return ModelFilter::filter($q, $data);
    }

    public function getDoctorSchedule(int $doctorId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return Appointment::where('doctor_id', $doctorId)
            ->with(['doctor', 'patient', 'type', 'room'])
            ->whereDate('start_time', $date)->orderBy('start_time', 'asc')->get();
    }

    public function getClinicDailySchedule(int $clinicId, $date = null)
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $appointments = Appointment::where('clinic_id', $clinicId)
            ->with(['doctor', 'patient', 'room', 'type'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time', 'asc')
            ->get();

            return $appointments;
    }
}
