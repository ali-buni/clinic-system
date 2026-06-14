<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDoctorSpecialtiesRequest;
use App\Models\Specialty;
use App\Services\DoctorSpecialtyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class DoctorSpecialtyController extends Controller
{
    protected DoctorSpecialtyService $doctorSpecialtyService;

    public function __construct(DoctorSpecialtyService $doctorSpecialtyService)
    {
        $this->doctorSpecialtyService = $doctorSpecialtyService;
    }

    /**
     * إضافة تخصصات جديدة (POST)
     */
    public function attachSpecialties(UpdateDoctorSpecialtiesRequest $request): JsonResponse
    {
        $current = $this->doctorSpecialtyService->attachSpecialties(
            $request->user()->doctorProfile()->first(),
            $request->validated('specialty_ids')
        );

        return response()->json(['success' => true, 'current_specialties' => $current], 200);
    }

    public function syncSpecialties(UpdateDoctorSpecialtiesRequest $request): JsonResponse
    {
        $current = $this->doctorSpecialtyService->syncSpecialties(
            $request->user()->doctorProfile()->first(),
            $request->validated('specialty_ids')
        );

        return response()->json(['success' => true, 'current_specialties' => $current], 200);
    }

    public function detachSpecialty(UpdateDoctorSpecialtiesRequest $request, Specialty $specialty): JsonResponse
    {
        $current = $this->doctorSpecialtyService->detachSpecialty(
            $request->user()->doctorProfile()->first(),
            $specialty->id
        );

        return response()->json(['success' => true, 'current_specialties' => $current], 200);
    }

    public function showDoctorSpecialties($userId): JsonResponse
    {
        $specialties = $this->doctorSpecialtyService->getDoctorSpecialties($userId + 1);

        return response()->json(['status' => true, 'specialties' => $specialties], 200);
    }



    public function showPrimary($userId): JsonResponse
    {
        $user = User::with('doctorProfile')->find($userId);
        if (!$user || !$user->doctorProfile) {
            return response()->json(['message' => 'Doctor not found.'], 404);
        }

        $primarySpecialty = $this->doctorSpecialtyService->getPrimarySpecialty($user->doctorProfile);

        return response()->json([
            'status' => true,
            'primary_specialty' => $primarySpecialty ?? 'No primary specialty assigned'
        ], 200);
    }

   public function changePrimary(Request $request, $specialtyId): JsonResponse
    {
        $user = auth()->user()->load('doctorProfile');
 
        if (!$user || !$user->doctorProfile) {
            return response()->json(['message' => 'Doctor profile not found.'], 404);
        }

        $updated = $this->doctorSpecialtyService->updatePrimarySpecialty(
            $user->doctorProfile, 
            $specialtyId
        );

        if (!$updated) {
            return response()->json([
                'status' => false,
                'message' => 'This specialty is not attached to your profile.'
            ], 422);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Primary specialty updated successfully.'
        ], 200);
    }
}
