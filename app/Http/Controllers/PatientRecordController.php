<?php

namespace App\Http\Controllers;

use App\Services\PatientRecordService;
use App\Services\ApiResponse;
use App\Actions\PatientRecord\{CreatePatientRecordAction, UpdatePatientRecordAction};
use App\Http\Resources\PatientRecordResource;
use App\Http\Requests\PatientRecord\{CreatePatientRecordRequest, UpdatePatientRecordRequest, GetAllRecordsFilteredRequest};
use App\Models\Patient_record;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterRequest;
use Exception;


class PatientRecordController extends Controller
{
    public function __construct(protected PatientRecordService $service) {}

    public function store(CreatePatientRecordRequest $request, CreatePatientRecordAction $action): JsonResponse
    {
        $record = $action->execute($request->validated());
        return ApiResponse::success(new PatientRecordResource($record), 'Record created successfully', 201);
    }

    public function update(UpdatePatientRecordRequest $request, UpdatePatientRecordAction $action, int $id): JsonResponse
    {
        try {
            $data = array_merge($request->validated(), ['record_id' => $id]);
            $record = $action->execute($data);

            return ApiResponse::success(
                new PatientRecordResource($record),
                'Record updated successfully'
            );
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return ApiResponse::error(
                    'Patient record not found',
                    404
                );
            }

            return ApiResponse::error(
                'Failed to update record',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $record = Patient_record::find($id);

            if (!$record) {
                return ApiResponse::error('Patient record not found', 404);
            }

            $record->delete();

            return ApiResponse::success(null, 'Record deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error(
                'Failed to delete record',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }

    public function index(GetAllRecordsFilteredRequest $request): JsonResponse
    {
        $records = $this->service->getAllFiltered(
            $request->validated()
        );

        if ($records->isEmpty()) {
            return ApiResponse::error('No patient records found', 404);
        }

        return ApiResponse::pagination(
            $records,
            'Records retrieved successfully'
        );
    }

    public function history(int $patientId): JsonResponse
    {
        if (!Patient::find($patientId)) {
            return ApiResponse::error('Patient not found', 404);
        }

        $history = $this->service->getPatientHistory($patientId);

        if ($history->isEmpty()) {
            return ApiResponse::error('No medical records found for this patient', 404);
        }

        return ApiResponse::success(
            PatientRecordResource::collection($history),
            'Patient history retrieved successfully'
        );
    }

    public function getByDoctor(int $patientId, int $doctorId): JsonResponse
    {
        if (!Patient::find($patientId)) {
            return ApiResponse::error('Patient not found', 404);
        }

        if (!Doctor::find($doctorId)) {
            return ApiResponse::error('Doctor not found', 404);
        }

        $records = $this->service->getRecordsByDoctor($patientId, $doctorId);

        if ($records->isEmpty()) {
            return ApiResponse::error('No records found for this doctor and patient', 404);
        }

        return ApiResponse::success(
            PatientRecordResource::collection($records),
            'Records retrieved successfully'
        );
    }

    public function getByRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'integer|exists:rooms,id'
        ]);

        $records = $this->service->getRecordsByRoom($validated['room_ids']);

        if ($records->isEmpty()) {
            return ApiResponse::error('No records found for the specified rooms', 404);
        }

        return ApiResponse::success(
            PatientRecordResource::collection($records),
            'Records retrieved successfully'
        );
    }

    public function getAllByDoctor(int $doctorId): JsonResponse
    {
        if (!\App\Models\Doctor::find($doctorId)) {
            return ApiResponse::error('Doctor not found', 404);
        }

        $records = $this->service->getAllByDoctor($doctorId);

        if ($records->isEmpty()) {
            return ApiResponse::error('No medical records found for this doctor', 404);
        }

        return ApiResponse::success(PatientRecordResource::collection($records), 'Doctor records retrieved successfully');
    }
}
