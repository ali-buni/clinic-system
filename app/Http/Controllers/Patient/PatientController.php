<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\PatientRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\Patient\PatientInfoResource;
use App\Http\Resources\Patient\PatientMedicalHistoryResource;
use App\Models\PatientInfo;
use App\Services\ApiResponse;
use App\Services\PatientService;
use App\Services\ModelFilter;
use Exception;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected PatientService $service;

    public function __construct(PatientService $service)
    {
        $this->service = $service;
    }

    public function index(FilterRequest $request)
    {
        $filters = $request->validated();

        $query = PatientInfo::query()->with('user');

        if (empty($filters['clinic_id'])) {
            return ApiResponse::error('Please enter the required valid clinic.');
        }
        $query->where('clinic_id', $filters['clinic_id']);
        $patients = ModelFilter::filter($query, $filters);

        return ApiResponse::pagination($patients, 'Patients retrieved successfully', PatientInfoResource::collection($patients));
    }

    public function indexTrashed(FilterRequest $request)
    {
        $filters = $request->validated();

        $query = PatientInfo::query()->onlyTrashed()->with('user');

        if (empty($filters['clinic_id'])) {
            return ApiResponse::error('Please enter the required valid clinic.');
        }
        $query->where('clinic_id', $filters['clinic_id']);
        $patients = ModelFilter::filter($query, $filters);

        return ApiResponse::pagination($patients, 'success', PatientInfoResource::collection($patients));
    }

    public function show($patientId)
    {
        try {
            $patient = $this->service->getById($patientId);
            return ApiResponse::success(new PatientInfoResource($patient), 'the patient data.');
        } catch (Exception $e) {
            return ApiResponse::error('The patient is not found.');
        }
    }

    public function update(PatientRequest $request)
    {
        $validated = $request->validated();
        $data = collect($validated)->except('patient_id')->toArray();

        $this->service->updatePatientInfo($validated['patient_id'], $data);
        return ApiResponse::success();
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|string|exists:patient_infos,id'
        ]);

        try {
            $del = $this->service->softDelete($validated['patient_id']);
            if ($del) {
                return ApiResponse::success();
            }
            return ApiResponse::error();
        } catch (Exception $e) {
            return ApiResponse::error('The patient is not found.');
        }
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|string|exists:patient_infos,id'
        ]);
        try {
            $restored = $this->service->restore($validated['patient_id']);
            if ($restored) {
                return ApiResponse::success();
            }
            return ApiResponse::error();
        } catch (Exception $e) {
            return ApiResponse::error('The patient is not found.');
        }
    }

    public function medicalHistory(int $patientId)
    {
        try {
            $patient = $this->service->getPatientMedicalHistory($patientId);
            if (!$patient) {
                return ApiResponse::error('Patient not found.');
            }
            return ApiResponse::success(new PatientMedicalHistoryResource($patient), 'Medical history retrieved successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve medical history.');
        }
    }
}
