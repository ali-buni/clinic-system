<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Jobs\SendAppointmentStatusNotificationJob;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Traits\BookingTrait;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                throw new Exception('the appointment is not scheduled');
            }

            if (Carbon::parse($appointment->start_time)->lte(Carbon::now()->addDay()->startOfDay())) {
                throw new Exception('Cannot cancel appointment less than 1 day before start time.');
            }
            $appointment->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason ?? 'no reason',
            ]);

            $bookingInvoice = Invoice::where('appointment_id', $appointment->id)
                ->byDescription('Booking fee')
                ->first();

            if ($bookingInvoice) {
                foreach ($bookingInvoice->completedPayments as $payment) {
                    if ($payment->getRefundableAmount() > 0) {
                        app(PaymentService::class)->refundPayment(
                            $payment->id,
                            $payment->getRefundableAmount(),
                            'Appointment cancelled',
                            auth()->id()
                        );
                    }
                }
                $bookingInvoice->update(['status' => InvoiceStatus::Void->value]);
            }

            Cache::increment("cache_v:doctor:{$appointment->doctor_id}:slot");

            SendAppointmentStatusNotificationJob::dispatch($appointment->id, 'cancelled', 'all');

            return true;
        });
    }

    public function completeAppointment(int $id)
    {
        return DB::transaction(function () use ($id) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'confirmed') {
                throw new Exception('the appointment is not confirmed');
            }
            $appointment->update(['status' => 'completed']);

            Cache::increment("cache_v:doctor:{$appointment->doctor_id}:slot");

            SendAppointmentStatusNotificationJob::dispatch($appointment->id, 'completed', 'secretary');

            return true;
        });
    }

    public function markConfirmed(int $id)
    {
        return DB::transaction(function () use ($id) {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'scheduled') {
                throw new Exception('the appointment is not scheduled');
            }
            $appointment->update(['status' => 'confirmed']);

            Cache::increment("cache_v:doctor:{$appointment->doctor_id}:slot");

            SendAppointmentStatusNotificationJob::dispatch($appointment->id, 'confirmed', 'all');

            return true;
        });
    }

    public function getAppointment(int $id): Appointment
    {
        return Appointment::with(['doctor', 'patient', 'room', 'type', 'invoices'])->findOrFail($id);
    }

    public function getPatientAppointments(int $patientId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('patient_id', $patientId)->with(['doctor', 'patient', 'room', 'type', 'invoices']);

        return ModelFilter::filter($q, $data);
    }

    public function getDoctorAppointments(int $doctorId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('doctor_id', $doctorId)->with(['patient', 'clinic', 'room', 'type', 'invoices']);

        return ModelFilter::filter($q, $data);
    }

    public function getClinicAppointments(int $clinicId, array $data): LengthAwarePaginator
    {
        $q = Appointment::where('clinic_id', $clinicId)
            ->with(['doctor', 'patient', 'room', 'type', 'invoices']);

        return ModelFilter::filter($q, $data);
    }

    public function getRoomAppointments(array $roomIds, array $data): LengthAwarePaginator
    {
        $q = Appointment::whereIn('room_id', $roomIds)->with(['doctor', 'patient', 'room', 'type', 'invoices']);

        return ModelFilter::filter($q, $data);
    }

    public function getDoctorSchedule(int $doctorId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return Appointment::where('doctor_id', $doctorId)
            ->with(['doctor', 'patient', 'type', 'room', 'invoices'])
            ->whereDate('start_time', $date)->orderBy('start_time', 'asc')->get();
    }

    public function getClinicDailySchedule(int $clinicId, $date = null)
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $appointments = Appointment::where('clinic_id', $clinicId)
            ->with(['doctor', 'patient', 'room', 'type', 'invoices'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        return $appointments;
    }
}
