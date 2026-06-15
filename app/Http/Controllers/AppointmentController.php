<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookAppointmentRequest;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\SlotResource;
use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Doctor;
use App\Services\AppointmentService;
use App\Services\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function book(BookAppointmentRequest $request)
    {
        $validated = $request->validated();

        try {
            $type = Appointment_type::findOrFail($validated['appointment_type_id']);
            $doctor = Doctor::findOrFail($validated['doctor_id']);

            $slotsCount = $type->types;
            $slotMinutes = $doctor->appointment_duration ?? 30;

            $end = Carbon::parse($validated['date'] . ' ' . $validated['start_time'])
                ->addMinutes($slotMinutes * $slotsCount)
                ->format('H:i');

            $data = $request->only([
                'clinic_id',
                'room_id',
                'appointment_type_id',
                'patient_id',
                'visit_reason',
                'notes',
            ]);

            $appointment = $this->appointmentService->bookAppointment(
                $validated['doctor_id'],
                $validated['date'],
                $validated['start_time'],
                $end,
                $data
            );

            return ApiResponse::success(new AppointmentResource($appointment), 'Appointment booked successfully', 201);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Related doctor or appointment type not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to process booking due to an internal server error', 500);
        }
    }

    public function reschedule(int $id, RescheduleAppointmentRequest $request)
    {
        $validated = $request->validated();

        try {
            $appointment = Appointment::findOrFail($id);

            $date = $validated['date'] ?? Carbon::parse($appointment->start_time)->format('Y-m-d');
            $startTime = $validated['start_time'] ?? Carbon::parse($appointment->start_time)->format('H:i');

            $durationMinutes = Carbon::parse($appointment->start_time)->diffInMinutes(Carbon::parse($appointment->end_time));

            $endTime = Carbon::parse($date . ' ' . $startTime)
                ->addMinutes($durationMinutes)
                ->format('H:i:s');

            $updatedAppointment = $this->appointmentService->updateAppointment(
                $id,
                $date,
                $startTime,
                $endTime,
            );

            return ApiResponse::success(new AppointmentResource($updatedAppointment), 'Appointment updated successfully');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update appointment due to an internal error', 500);
        }
    }

    public function cancel(int $id, CancelAppointmentRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->appointmentService->cancelAppointment((int)$id, $validated['cancel_reason'] ?? 'no reason');
            return ApiResponse::success(null, 'Appointment cancelled');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to cancel appointment', 500);
        }
    }

    public function complete(int $id)
    {
        try {
            $this->appointmentService->completeAppointment((int)$id);
            return ApiResponse::success(null, 'Appointment completed');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to complete appointment', 500);
        }
    }

    public function markConfirmed(int $id)
    {
        try {
            $appointment = $this->appointmentService->markConfirmed((int)$id);
            return ApiResponse::success(null, 'Appointment marked confirmed');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to mark confirmed', 500);
        }
    }

    public function show(int $id)
    {
        try {
            $appointment = $this->appointmentService->getAppointment((int)$id);
            return ApiResponse::success(new AppointmentResource($appointment));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch appointment', 500);
        }
    }

    public function patientAppointments(int $patientId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getPatientAppointments((int)$patientId, $validated);
            return ApiResponse::pagination($data, 'Patient appointments', AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch patient appointments', 500);
        }
    }

    public function doctorAppointments(int $doctorId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getDoctorAppointments((int)$doctorId, $validated);
            return ApiResponse::pagination($data, 'Doctor appointments', AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch doctor appointments', 500);
        }
    }

    public function clinicAppointments(int $clinicId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getClinicAppointments((int)$clinicId, $validated);
            return ApiResponse::pagination($data, 'Clinic appointments', AppointmentResource::collection($data->items()));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch clinic appointments', 500);
        }
    }

    public function roomAppointments(FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $roomIds = $validated['roomIds'];
            $data = $this->appointmentService->getRoomAppointments($roomIds, $validated);
            return ApiResponse::pagination($data, 'Room appointments', AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch room appointments', 500);
        }
    }


    public function availableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,id',
            'date' => 'required|date|date_format:Y-m-d|after:now',
        ]);

        try {
            $slots = $this->appointmentService->getAvailableSlots((int)$request->doctor_id, $request->date);
            if (! $slots) {
                return ApiResponse::error('No slots found for this day or no work day exists');
            }
            // Manual structure if additional() gives trouble
            $data = [
                'data' => SlotResource::collection($slots),
                'meta' => [
                    'date' => Carbon::parse($request->date)->format("Y-m-d"),
                    'dayOfWeek' => Carbon::parse($request->date)->dayOfWeek,
                    'nums' => count($slots),
                ]
            ];
            return ApiResponse::success($data);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch available slots' . $e->getMessage(), 500);
        }
    }

    public function doctorSchedule(int $doctorId, Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|date_format:Y-m-d'
        ]);
        $date = $request->get('date');
        try {
            $data = $this->appointmentService->getDoctorSchedule((int)$doctorId, $date);
            if (count($data) === 0) {
                $date = $date ?? now()->format('Y-m-d');
                return ApiResponse::error("no appointment for this date {$date}", 400);
            }
            return ApiResponse::success(AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch doctor schedule', 500);
        }
    }

    public function clinicSchedule(int $clinicId, Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|date_format:Y-m-d'
        ]);
        $date = $request->get('date');
        try {
            $data = $this->appointmentService->getClinicDailySchedule((int)$clinicId, $date);
            if (count($data) === 0) {
                return ApiResponse::error('no appointment for this date ' . $date, 400);
            }
            return ApiResponse::success(AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch clinic schedule', 500);
        }
    }
}
