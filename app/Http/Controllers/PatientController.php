<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\ApiResponse;
use App\Services\PatientService;
use App\Services\ModelFilter;

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
            return ApiResponse::error('Please enter the required clinic.');
        }
        $query->where('clinic_id', $filters['clinic_id']);
        $patients = ModelFilter::filter($query, $filters);

        return ApiResponse::success(PatientResource::collection($patients));
    }

    public function store(PatientRequest $request)
    {
        $auth = $this->authorizeRole('secretary');
        if ($auth !== true) {
            return $auth;
        }
        $auth = $this->authorizePermission('create patients');
        if ($auth !== true) {
            return $auth;
        }

        $patient = $this->service->create($request->validated());

        return ApiResponse::success();
    }

    public function show($patientId)
    {
        $patient = $this->service->getById($patientId);
        return ApiResponse::success(new PatientResource($patient), 'the patient data.');
    }

    public function update(PatientRequest $request, $patientId)
    {
        $auth = $this->authorizeRole('secretary');
        if ($auth !== true) {
            return $auth;
        }
        $auth = $this->authorizePermission('edit patients');
        if ($auth !== true) {
            return $auth;
        }

        $this->service->update($patientId, $request->validated());
        return ApiResponse::success();
    }

    public function destroy($patientId)
    {
        $auth = $this->authorizeRole('secretary');
        if ($auth !== true) {
            return $auth;
        }
        $auth = $this->authorizePermission('delete patients');
        if ($auth !== true) {
            return $auth;
        }

        $this->service->softDelete($patientId);
        return ApiResponse::success();
    }

    public function restore($patientId)
    {
        $auth = $this->authorizeRole('secretary');
        if ($auth !== true) {
            return $auth;
        }
        $auth = $this->authorizePermission('delete patients');
        if ($auth !== true) {
            return $auth;
        }

        $this->service->softDelete($patientId);
        return ApiResponse::success();
    }
}
