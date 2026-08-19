<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Appointment\SlotResource;
use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Doctor;
use App\Services\ApiResponse;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
            $room_id = $doctor->room_id;
            if ($room_id == null) {
                return ApiResponse::error('This doctor is invalid');
            }
            $slotsCount = $type->types;
            $slotMinutes = $doctor->appointment_duration ?? 30;

            $end = Carbon::parse($validated['date'].' '.$validated['start_time'])
                ->addMinutes($slotMinutes * $slotsCount)
                ->format('H:i');

            $data = $request->only([
                'clinic_id',
                'appointment_type_id',
                'patient_id',
                'visit_reason',
                'notes',
            ]);
            $data['room_id'] = $room_id;

            $appointment = $this->appointmentService->bookAppointment(
                $validated['doctor_id'],
                $validated['date'],
                $validated['start_time'],
                $end,
                $data
            );

            $appointment->load('invoices');

            return ApiResponse::success([
                'appointment' => new AppointmentResource($appointment),
                'invoices' => $appointment->invoices->map(fn ($inv) => [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'total_cost' => $inv->total_cost,
                    'status' => $inv->status,
                    'description' => $inv->description,
                ]),
            ], 'Appointment booked successfully', 201);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Related doctor or appointment type not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to process booking due to an internal server error', 500);
        }
    }

    public function reschedule($id, RescheduleAppointmentRequest $request)
    {
        $validated = $request->validated();

        try {
            $appointment = Appointment::findOrFail((int) $id);
            $type = Appointment_type::findOrFail($validated['type_id'] ?? $appointment->appointment_type_id);
            $doctor = Doctor::findOrFail($appointment->doctor_id);

            $date = Carbon::parse($validated['date'])->format('Y-m-d');
            $start = $validated['start_time'];

            $slotsCount = $type->types;
            $slotMinutes = $doctor->appointment_duration ?? 30;

            $end = Carbon::parse($validated['date'].' '.$validated['start_time'])
                ->addMinutes($slotMinutes * $slotsCount)
                ->format('H:i');

            $updatedAppointment = $this->appointmentService->updateAppointment(
                $id,
                $date,
                $start,
                $end,
            );

            $updatedAppointment->load('invoices');

            return ApiResponse::success([
                'appointment' => new AppointmentResource($updatedAppointment),
                'invoices' => $updatedAppointment->invoices->map(fn ($inv) => [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'total_cost' => $inv->total_cost,
                    'status' => $inv->status,
                    'description' => $inv->description,
                ]),
            ], 'Appointment updated successfully');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to update appointment due to an internal error', 500);
        }
    }

    public function cancel($id, CancelAppointmentRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->appointmentService->cancelAppointment((int) $id, $validated['cancel_reason'] ?? 'no reason');

            return ApiResponse::success(null, 'Appointment cancelled');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to cancel appointment', 500);
        }
    }

    public function complete($id)
    {
        try {
            $this->appointmentService->completeAppointment((int) $id);

            return ApiResponse::success(null, 'Appointment completed');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to complete appointment', 500);
        }
    }

    public function markConfirmed($id)
    {
        try {
            $appointment = $this->appointmentService->markConfirmed((int) $id);

            return ApiResponse::success(null, 'Appointment marked confirmed');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to mark confirmed', 500);
        }
    }

    public function show($id)
    {
        try {
            $appointment = $this->appointmentService->getAppointment((int) $id);

            return ApiResponse::success(new AppointmentResource($appointment));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Appointment not found', 404);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch appointment', 500);
        }
    }

    public function patientAppointments($patientId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getPatientAppointments((int) $patientId, $validated);

            return ApiResponse::pagination($data, 'Patient appointments', AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch patient appointments', 500);
        }
    }

    

    public function doctorAppointments($doctorId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getDoctorAppointments((int) $doctorId, $validated);

            return ApiResponse::pagination($data, 'Doctor appointments', AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch doctor appointments', 500);
        }
    }

    public function clinicAppointments($clinicId, FilterRequest $request)
    {
        $validated = $request->validated();
        try {
            $data = $this->appointmentService->getClinicAppointments((int) $clinicId, $validated);

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
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        try {
            $slots = $this->appointmentService->getAvailableSlots((int) $request->doctor_id, $request->date);
            if (! $slots) {
                return ApiResponse::error('No slots found for this day or no work day exists', 400);
            }
            $data = [
                'data' => SlotResource::collection($slots),
                'meta' => [
                    'date' => Carbon::parse($request->date)->format('Y-m-d'),
                    'dayOfWeek' => Carbon::parse($request->date)->dayOfWeek,
                    'dayOfWeekEN' => Carbon::parse($request->date)->englishDayOfWeek,
                    'count' => count($slots),
                ],
            ];

            return ApiResponse::success($data);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch available slots'.$e->getMessage(), 500);
        }
    }

    public function doctorSchedule($doctorId, Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|date_format:Y-m-d',
        ]);
        $date = $request->get('date');
        try {
            $data = $this->appointmentService->getDoctorSchedule((int) $doctorId, $date);
            if (count($data) === 0) {
                $date = $date ?? now()->format('Y-m-d');

                return ApiResponse::error("no appointment for this date {$date}", 400);
            }

            return ApiResponse::success(AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch doctor schedule', 500);
        }
    }

    public function clinicSchedule($clinicId, Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|date_format:Y-m-d',
        ]);
        $date = $request->get('date');
        try {
            $data = $this->appointmentService->getClinicDailySchedule((int) $clinicId, $date);
            if (count($data) === 0) {
                return ApiResponse::error('no appointment for this date '.$date, 400);
            }

            return ApiResponse::success(AppointmentResource::collection($data));
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch clinic schedule', 500);
        }
    }
}
