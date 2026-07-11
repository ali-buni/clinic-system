<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\SearchDoctorRequest;
use App\Http\Resources\Doctor\PatientDoctorSearchResource;
use App\Jobs\LogActivityJob;
use App\Models\Doctor;
use App\Services\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientDoctorSearchController extends Controller
{
    public function search(SearchDoctorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $query = Doctor::query()
                ->with(['user', 'clinic', 'specialties']);

            if ($name = $validated['name'] ?? null) {
                $query->whereHas('user', function ($q) use ($name) {
                    $q->where('fname', 'LIKE', "%{$name}%")
                        ->orWhere('lname', 'LIKE', "%{$name}%");
                });
            }

            if ($location = $validated['location'] ?? null) {
                $query->whereHas('clinic', function ($q) use ($location) {
                    $q->where('location', 'LIKE', "%{$location}%");
                });
            }

            if ($specialty = $validated['specialty'] ?? null) {
                $query->whereHas('specialties', function ($q) use ($specialty) {
                    $q->where('en_name', 'LIKE', "%{$specialty}%")
                        ->orWhere('ar_name', 'LIKE', "%{$specialty}%");
                });
            }

            if (isset($validated['consultation_fee_min'])) {
                $query->where('consultation_fee', '>=', $validated['consultation_fee_min']);
            }

            if (isset($validated['consultation_fee_max'])) {
                $query->where('consultation_fee', '<=', $validated['consultation_fee_max']);
            }

            $sortBy = $validated['sort_by'] ?? null;
            $direction = $validated['sort_direction'] ?? 'asc';

            if ($sortBy) {
                if ($sortBy === 'name') {
                    $query->join('users', 'doctors.user_id', '=', 'users.id')
                        ->orderBy('users.fname', $direction);
                } else {
                    $query->orderBy($sortBy, $direction);
                }
            }

            $result = $query->paginate($validated['per_page'] ?? 15);

            $patient = Auth::user()->patientInfo;
            if ($patient) {
                LogActivityJob::dispatch(
                    'doctor',
                    'patient searched doctors',
                    get_class($patient),
                    $patient->id,
                    Auth::id(),
                    [
                        'filters' => $validated,
                        'results_count' => $result->total(),
                    ],
                    'searched'
                );
            }

            return ApiResponse::pagination(
                $result,
                'Doctors retrieved successfully.',
                PatientDoctorSearchResource::collection($result)
            );
        } catch (\Exception $e) {
            $patient = Auth::user()->patientInfo ?? null;
            LogActivityJob::dispatch(
                'doctor',
                'patient doctor search failed',
                'App\\Models\\PatientInfo',
                $patient?->id ?? 0,
                Auth::id(),
                ['error' => $e->getMessage()],
                'failed'
            );

            return ApiResponse::error(
                'Failed to search doctors.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }
}
