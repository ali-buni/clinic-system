<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicRequest;
use App\Http\Requests\NewDoctorRequest;
use App\Http\Requests\NewSecretaryRequest;
use App\Http\Resources\ClinicResource;
use App\Services\ApiResponse;
use App\Services\ClinicServices;
use Illuminate\Support\Facades\Auth;

class ClinicController extends Controller
{
    protected ClinicServices $clinicServices;

    public function __construct(ClinicServices $clinicServices)
    {
        $this->clinicServices = $clinicServices;
    }

    public function clinicInfo()
    {
        $clinic = $this->clinicServices->getClinicInfoByOwner(Auth::id());

        if (!$clinic) {
            return ApiResponse::error('Clinic not found for the authenticated owner', 404);
        }

        return ApiResponse::success(new ClinicResource($clinic));
    }

    public function updateClinic(ClinicRequest $request, $clinicId)
    {
        $validated = $request->validated();
        $updated = $this->clinicServices->updateClinicInfo($clinicId, $validated);

        if (!$updated) {
            return ApiResponse::error('Clinic update failed.', 422);
        }
        return ApiResponse::success(null, 'Clinic updated successfully.');
    }

    public function createDoctor(NewDoctorRequest $request)
    {
        $data = $request->validated();
        // $data = array_merge($validated, ['clinic_id' => Auth::user()->clinic->id]);

        $created = $this->clinicServices->createDoctor($data);

        if (!$created) {
            return ApiResponse::error('Failed to create doctor.', 422);
        }

        return ApiResponse::success(null, 'Doctor created successfully.');
    }

    public function createSecretary(NewSecretaryRequest $request)
    {
        $data = $request->validated();
        // $data = array_merge($validated, ['clinic_id' => Auth::user()->clinic->id]);

        $created = $this->clinicServices->createSecretary($data);

        if (!$created) {
            return ApiResponse::error('Failed to create secretary.', 422);
        }

        return ApiResponse::success(null, 'Secretary created successfully.');
    }
}
