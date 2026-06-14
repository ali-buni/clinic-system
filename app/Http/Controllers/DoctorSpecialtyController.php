<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDoctorSpecialtiesRequest;
use App\Http\Resources\SpecialResource;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Services\DoctorSpecialtyService;
use Illuminate\Http\JsonResponse;
use App\Services\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class DoctorSpecialtyController extends Controller
{
    protected DoctorSpecialtyService $doctorSpecialtyService;

    public function __construct(DoctorSpecialtyService $doctorSpecialtyService)
    {
        $this->doctorSpecialtyService = $doctorSpecialtyService;
    }

    public function attachSpecialties(UpdateDoctorSpecialtiesRequest $request): JsonResponse
    {
        $doctor = $request->user()->doctorProfile;
        if (! $doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $current = $this->doctorSpecialtyService->attachSpecialties($doctor, $request->validated()['specialty_ids']);
            return ApiResponse::success(SpecialResource::collection($current), 'current_specialties');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to attach specialties.');
        }
    }

    public function detachSpecialty(int $specialId): JsonResponse
    {
        $doctor = Auth::user()->doctorProfile;
        if (! $doctor) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        try {
            $current = $this->doctorSpecialtyService->detachSpecialty($doctor, $specialId);
            return ApiResponse::success(SpecialResource::collection($current), 'Specialty detached successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to detach specialty.');
        }
    }

    public function showDoctorSpecialties(): JsonResponse
    {
        $userId = Auth::user()->id;
        try {
            $specialties = $this->doctorSpecialtyService->getDoctorSpecialties((int) $userId);
            return ApiResponse::success(SpecialResource::collection($specialties), 'Doctor specialties retrieved', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Doctor not found.', 404);
        } catch (\Throwable $e) {
            return ApiResponse::error('Server error.', 500);
        }
    }



    public function showPrimary(int $doctorId): JsonResponse
    {
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return ApiResponse::error('Doctor not found.', 404);
        }
        try {
            $primarySpecialty = $this->doctorSpecialtyService->getPrimarySpecialty($doctor);
            return ApiResponse::success(new SpecialResource($primarySpecialty), 'Primary specialty retrieved', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function changePrimary(int $specialtyId): JsonResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->doctorProfile) {
            return ApiResponse::error('Doctor profile not found.', 404);
        }

        $specialty = Specialty::find($specialtyId);
        if (! $specialty) {
            return ApiResponse::error('Specialty not found.', 404);
        }
        $updated = $this->doctorSpecialtyService->updatePrimarySpecialty($user->doctorProfile, (int) $specialtyId);

        if (! $updated) {
            return ApiResponse::error('This specialty is not attached to your profile.', 422);
        }
        return ApiResponse::success(null, 'Primary specialty updated successfully', 200);
    }

    public function index(): JsonResponse
    {
        try {
            $specialties = Specialty::query()
                ->select(['id', 'ar_name', 'en_name'])
                ->get();

            return ApiResponse::success($specialties, 'Specialties retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve specialties', 500);
        }
    }
}
