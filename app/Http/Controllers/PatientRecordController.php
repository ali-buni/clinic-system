<?php

namespace App\Http\Controllers;

use App\Actions\PatientRecord\CreatePatientRecordAction;
use App\Actions\PatientRecord\UpdatePatientRecordAction;
use App\Http\Requests\PatientRecord\{
    CreatePatientRecordRequest,
    UpdatePatientRecordRequest,
    DeletePatientRecordRequest,
    GetAllRecordsFilteredRequest
};
use App\Http\Resources\PatientRecordResource;
use App\Models\Patient_record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Request;

class PatientRecordController extends Controller
{
    public function createPatientRecord(
        CreatePatientRecordRequest $request,
        CreatePatientRecordAction $action
    ): JsonResponse {
        try {
            $record = $action->execute($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Patient record created successfully.',
                'data'    => new PatientRecordResource($record),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create patient record.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }


    public function updatePatientRecord(
        UpdatePatientRecordRequest $request,
        UpdatePatientRecordAction $action,
        $record_id
    ): JsonResponse {
        try {
            $data = $request->validated();

            $data['record_id'] = $record_id;

            $record = $action->execute($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Patient record updated successfully.',
                'data'    => new PatientRecordResource($record),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update patient record.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }


    public function deletePatientRecord(Request $request, $record_id): JsonResponse
    {
        try {
            $record = Patient_record::findOrFail($record_id);
            $record->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Patient record deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete patient record.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }


    public function getAllRecordsFiltered(GetAllRecordsFilteredRequest $request): AnonymousResourceCollection
    {
        $query = Patient_record::query()
            ->with(['diseases', 'prescriptions.items', 'patient', 'doctor']);

        if ($request->filled('disease_code')) {
            $query->whereHas('diseases', fn($q) =>
                $q->where('code', $request->disease_code)
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->latest()->paginate(15);

        return PatientRecordResource::collection($records);
    }


    public function getPatientHistory(int $patient_id): AnonymousResourceCollection
    {
        $records = Patient_record::with(['diseases', 'prescriptions.items', 'doctor'])
            ->where('patient_id', $patient_id)
            ->latest()
            ->get();

        return PatientRecordResource::collection($records);
    }

    public function getPatientRecordsByDoctor(int $patient_id, int $doctor_id): AnonymousResourceCollection
    {
        $records = Patient_record::with(['diseases', 'prescriptions'])
            ->where('patient_id', $patient_id)
            ->where('doctor_id', $doctor_id)
            ->latest()
            ->get();

        return PatientRecordResource::collection($records);
    }

    public function getRecordsByRoom(int $room_id): AnonymousResourceCollection
    {
        $records = Patient_record::with(['patient', 'doctor', 'diseases'])
            ->whereHas('doctor', fn($q) => $q->where('room_id', $room_id))
            ->latest()
            ->get();

        return PatientRecordResource::collection($records);
    }
}
