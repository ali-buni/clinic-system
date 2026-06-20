<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkHourRequest;
use App\Http\Resources\WorkHoursResource;
use App\Models\Doctor;
use App\Services\DoctorScheduleService;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    protected DoctorScheduleService $scheduleService;

    public function __construct(DoctorScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function store(StoreWorkHourRequest $request): JsonResponse
    {
        $doctor = Auth::user()?->doctorProfile;
        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $workHour = $this->scheduleService->createWorkHour($doctor, $request->validated());

            return ApiResponse::success(new WorkHoursResource($workHour), 'Work hour added successfully.', 201);
        } catch (Exception $e) {
            $message = match ($e->getMessage()) {
                'day_already_exists' => 'لا يمكن إضافة هذا اليوم. يوجد بالفعل دوام محدد لهذا اليوم!',
                'room_conflict'      => 'تعذر الإضافة. هذا الوقت يتضارب مع دوام طبيب آخر في نفس الغرفة!',
                default              => 'حدث خطأ غير متوقع.'
            };

            return ApiResponse::error($message, 422);
        }
    }

    public function update(StoreWorkHourRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $doctor = Doctor::where('doctor_id', $validated['doctor_id'])->first();
        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }
        try {
            $workHour = $this->scheduleService->updateWorkHour($doctor, $validated['day_of_week'], $validated);

            return ApiResponse::success(new WorkHoursResource($workHour), 'Work hour updated successfully.');
        } catch (Exception $e) {
            return match ($e->getMessage()) {
                'day_already_exists'    => ApiResponse::error('لا يمكن إضافة هذا اليوم. يوجد بالفعل دوام محدد لهذا اليوم!', 422),
                'record_not_found'      => ApiResponse::error('Record not found or unauthorized.', 404),
                'appointment_conflict'  => ApiResponse::error('تعذر التعديل. يوجد مواعيد مستقبلية مجدولة للمرضى ضمن هذا الوقت!', 422),
                'room_conflict'         => ApiResponse::error('تعذر التعديل. الأوقات تتضارب مع طبيب آخر في نفس الغرفة!', 422),
                default                 => ApiResponse::error('حدث خطأ غير متوقع بالسيستم.', 500),
            };
        }
    }

    public function destroy(int $dayOfWeek, int $doctorId): JsonResponse
    {
        $doctor = Doctor::where('doctor_id', $doctorId)->first();
        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $this->scheduleService->deleteWorkHour($doctor, $dayOfWeek);

            return ApiResponse::success(null, 'Work hour deleted successfully (Soft Deleted).');
        } catch (Exception $e) {
            return match ($e->getMessage()) {
                'record_not_found'      => ApiResponse::error('Record not found or unauthorized.', 404),
                'appointment_conflict'  => ApiResponse::error('لا يمكن حذف هذا اليوم. يوجد مواعيد مستقبلية مجدولة للمرضى في هذا الوقت!', 422),
                default                 => ApiResponse::error('حدث خطأ غير متوقع بالسيستم.', 500),
            };
        }
    }

    public function getWeeklySchedule(int $doctorId): JsonResponse
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }
        $schedules = $this->scheduleService->getDoctorWeeklySchedule($doctor);
        if (count($schedules) === 0) {
            return ApiResponse::error('no schedules set for this doctor');
        }
        return ApiResponse::success(WorkHoursResource::collection($schedules), 'Schedule retrieved successfully.');
    }

    public function getWorkHourByDate(Request $request, int $doctorId): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }
        $workHour = $this->scheduleService->getDoctorWorkHourByDate($doctor, $request->input('date'));

        if (!$workHour) {
            return ApiResponse::error('no schedules set for this date');
        }
        return ApiResponse::success(
            (new WorkHoursResource($workHour))->additional(['date' => $request->input('date')]),
            'Work hour retrieved successfully.'
        );
    }
}
