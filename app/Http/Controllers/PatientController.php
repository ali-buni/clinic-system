<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
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

        $query = Patient::query();

        if (empty($filters['clinic_id'])) {
            return ApiResponse::error('Please enter the required valid clinic.');
        }
        $query->where('clinic_id', $filters['clinic_id']);
        $patients = ModelFilter::filter($query, $filters);

        return ApiResponse::pagination($patients, PatientResource::collection($patients));
    }

    public function indexTrashed(FilterRequest $request)
    {
        $filters = $request->validated();

        $query = Patient::query()->onlyTrashed();

        if (empty($filters['clinic_id'])) {
            return ApiResponse::error('Please enter the required valid clinic.');
        }
        $query->where('clinic_id', $filters['clinic_id']);
        $patients = ModelFilter::filter($query, $filters);

        return ApiResponse::pagination($patients, 'success', PatientResource::collection($patients));
    }

    public function store(PatientRequest $request)
    {
        $validated = $request->validated();
        // del $validated['patient_id'];
        $auth = $this->authorizePermission('create patients');
        if ($auth !== true) {
            return $auth;
        }

        $this->service->create($validated);

        return ApiResponse::success();
    }

    public function show($patientId)
    {
        try {
            $patient = $this->service->getById($patientId);
            return ApiResponse::success(new PatientResource($patient), 'the patient data.');
        } catch (Exception $e) {
            return ApiResponse::error('The patient in not found.');
        }
    }

    public function update(PatientRequest $request)
    {
        $validated = $request->validated();
        $data = collect($validated)->except('patient_id')->toArray();

        $auth = $this->authorizePermission('edit patients');
        if ($auth !== true) {
            return $auth;
        }

        $this->service->update($validated['patient_id'], $data);
        return ApiResponse::success();
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|string|exists:patients,id'
        ]);

        try {
            $auth = $this->authorizePermission('delete patients');
            if ($auth !== true) {
                return $auth;
            }

            $del = $this->service->softDelete($validated['patient_id']);
            if ($del) {
                return ApiResponse::success();
            }
            return ApiResponse::error();
        } catch (Exception $e) {
            return ApiResponse::error('The patient in not found.');
        }
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|string|exists:patients,id'
        ]);

        try {
            $auth = $this->authorizePermission('delete patients');
            if ($auth !== true) {
                return $auth;
            }

            $restored = $this->service->restore($validated['patient_id']);
            if ($restored) {
                return ApiResponse::success();
            }
            return ApiResponse::error();
        } catch (Exception $e) {
            return ApiResponse::error('The patient in not found.');
        }
    }
}
