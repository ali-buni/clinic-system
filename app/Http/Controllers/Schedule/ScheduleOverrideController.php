<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleOverrideRequest;
use App\Http\Resources\Schedule\ScheduleOverrideResource;
use App\Models\Doctor;
use App\Services\ApiResponse;
use App\Services\ScheduleOverrideService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleOverrideController extends Controller
{
    public function __construct(private ScheduleOverrideService $overrideService) {}

    public function store(StoreScheduleOverrideRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $doctor = Doctor::find($validated['doctor_id']);

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $override = $this->overrideService->create($doctor, $validated);
            return ApiResponse::success(new ScheduleOverrideResource($override), 'Override added successfully.', 201);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    public function update(int $id, StoreScheduleOverrideRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $doctor = Doctor::find($validated['doctor_id'] ?? $request->input('doctor_id'));

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $override = $this->overrideService->update($doctor, $id, $validated);
            return ApiResponse::success(new ScheduleOverrideResource($override), 'Override updated successfully.');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $doctorId = $request->input('doctor_id');
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $this->overrideService->delete($doctor, $id);
            return ApiResponse::success(null, 'Override deleted successfully.');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $doctorId = $request->input('doctor_id');
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        $override = $this->overrideService->get($doctor, $id);

        if (!$override) {
            return ApiResponse::error('Override not found.', 404);
        }

        return ApiResponse::success(new ScheduleOverrideResource($override));
    }

    public function index(Request $request): JsonResponse
    {
        $doctorId = $request->input('doctor_id');
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        $overrides = $this->overrideService->getByDoctor($doctor);

        return ApiResponse::success(ScheduleOverrideResource::collection($overrides), 'Overrides retrieved successfully.');
    }

    public function getByDate(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,id',
            'date'      => 'required|date_format:Y-m-d',
        ]);

        $doctor = Doctor::find($request->input('doctor_id'));

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        $override = $this->overrideService->getByDate($doctor, $request->input('date'));

        if (!$override) {
            return ApiResponse::error('No override for this date.', 404);
        }

        return ApiResponse::success(new ScheduleOverrideResource($override), 'Override retrieved successfully.');
    }

    public function getByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $doctor = Doctor::find($request->input('doctor_id'));

        if (!$doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        $overrides = $this->overrideService->getByDateRange(
            $doctor,
            $request->input('from'),
            $request->input('to')
        );

        return ApiResponse::success(ScheduleOverrideResource::collection($overrides), 'Overrides retrieved successfully.');
    }

    private function handleError(Exception $e): JsonResponse
    {
        return match ($e->getMessage()) {
            'record_not_found'       => ApiResponse::error('السجل غير موجود.', 404),
            'override_date_conflict' => ApiResponse::error('يوجد بالفعل استثناء لهذا التاريخ.', 422),
            'override_time_conflict' => ApiResponse::error('الأوقات المدخلة تتعارض مع استثناء موجود مسبقاً.', 422),
            default                  => ApiResponse::error($e->getMessage(), 500),
        };
    }
}
