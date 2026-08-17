<?php

namespace App\Http\Middleware;

use App\Models\Doctor;
use App\Models\PatientInfo;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Schedule_override;
use App\Models\Secretary;
use App\Models\User;
use App\Models\Work_hour;
use App\Services\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckResourceAccess
{
    public function handle(Request $request, Closure $next, ...$params)
    {
        if (! auth()->check()) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        $user = auth()->user();
        $first = $params[0] ?? '';
        $parts = explode(':', $first, 2);
        $rule = $parts[0] ?? null;
        $args = $parts[1] ?? '';

        if (! $rule) {
            return $next($request);
        }

        $response = match ($rule) {
            'doctor_self' => $this->checkDoctorSelf($user, $request, $args),
            'patient_self' => $this->checkPatientSelf($user, $request, $args),
            'secretary_rooms' => $this->checkSecretaryRooms($user, $request, $args),
            'owns' => $this->checkOwns($user, $request, $args),
            'owner_clinic' => $this->checkOwnerClinic($user, $request, $args),
            default => ApiResponse::error('Invalid resource access rule.', 500),
        };

        return $response ?? $next($request);
    }

    private function resolveParam(Request $request, string $name): mixed
    {
        $param = $request->route($name) ?? $request->input($name);

        if ($param == null) {
            $user = auth()->user();

            if ($user->hasRole('doctor')) {
                $param = $user->doctorProfile?->id;
            } elseif ($user->hasRole('patient')) {
                $param = $user->patientProfile?->id;
            } elseif ($user->hasRole('owner')) {
                $param = $user->clinicOwner?->id;
            } else if ($user->hasRole('secretary')) {
                $param = $user->secretaryProfile?->id;
            }
        }
        return $param;
    }

    private function checkDoctorSelf(User $user, Request $request, string $paramName): ?JsonResponse
    {
        $value = $this->resolveParam($request, $paramName);
        if (is_null($value)) {
            return ApiResponse::permissionDenied();
        }

        if ($user->hasRole('doctor')) {
            $doctorId = $user->doctorProfile?->id;
            if (! $doctorId || (int) $value !== $doctorId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('secretary')) {
            $secretary = $user->secretaryProfile;
            if (! $secretary) {
                return ApiResponse::permissionDenied();
            }
            $doctor = Doctor::find((int) $value);
            if (! $doctor || ! $doctor->room_id) {
                return ApiResponse::permissionDenied();
            }
            $assignedRoomIds = $secretary->rooms()->pluck('rooms.id')->toArray();
            if (! in_array($doctor->room_id, $assignedRoomIds)) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('owner')) {
            $ownedClinicId = $user->clinicOwner?->id;
            if (! $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }
            $doctorModel = Doctor::find((int) $value);
            if (! $doctorModel || ! $doctorModel->clinic_id || (int) $doctorModel->clinic_id !== $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        return ApiResponse::permissionDenied();
    }

    private function checkPatientSelf($user, Request $request, string $paramName): ?JsonResponse
    {
        $value = $this->resolveParam($request, $paramName);
        if (is_null($value)) {
            return ApiResponse::permissionDenied();
        }

        if ($user->hasRole('patient')) {
            $patientId = $user->patientProfile?->id;
            if (! $patientId || (int) $value !== $patientId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('owner')) {
            $ownedClinicId = $user->clinicOwner?->id;
            if (! $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }
            $patient = PatientInfo::find((int) $value);
            if (! $patient) {
                return ApiResponse::error('Resource not found.', 404);
            }
            if (! $patient->appointments()->where('clinic_id', $ownedClinicId)->exists()) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('doctor')) {
            $doctorId = $user->doctorProfile?->id;
            if (! $doctorId) {
                return ApiResponse::permissionDenied();
            }
            $patient = PatientInfo::find((int) $value);
            if (! $patient) {
                return ApiResponse::error('Resource not found.', 404);
            }
            if (! $patient->appointments()->where('doctor_id', $doctorId)->exists()) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('secretary')) {
            $secretary = $user->secretaryProfile;
            if (! $secretary) {
                return ApiResponse::permissionDenied();
            }
            $assignedRoomIds = $secretary->rooms()->pluck('rooms.id')->toArray();
            $patient = PatientInfo::find((int) $value);
            if (! $patient) {
                return ApiResponse::error('Resource not found.', 404);
            }
            if (! $patient->appointments()->whereIn('room_id', $assignedRoomIds)->exists()) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        return ApiResponse::permissionDenied();
    }

    private function checkSecretaryRooms(User $user, Request $request, string $paramName): ?JsonResponse
    {
        $value = $this->resolveParam($request, $paramName);
        if (is_null($value)) {
            return ApiResponse::permissionDenied();
        }

        if ($user->hasRole('owner')) {
            $ownedClinicId = $user->clinicOwner?->id;
            if (! $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }

            $requestedIds = is_array($value) ? $value : [(int) $value];
            $count = Room::whereIn('id', $requestedIds)
                ->where('clinic_id', $ownedClinicId)
                ->count();

            if ($count !== count($requestedIds)) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('secretary')) {
            $secretary = $user->secretaryProfile;
            if (! $secretary) {
                return ApiResponse::permissionDenied();
            }

            $requestedIds = is_array($value) ? $value : [(int) $value];
            $assignedIds = $secretary->rooms()->pluck('rooms.id')->toArray();

            if (array_diff($requestedIds, $assignedIds)) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('doctor')) {
            $doctor = $user->doctorProfile;
            if (! $doctor || ! $doctor->room_id) {
                return ApiResponse::permissionDenied();
            }

            $requestedIds = array_map('intval', is_array($value) ? $value : [(int) $value]);
            if (array_diff($requestedIds, [(int) $doctor->room_id])) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        return ApiResponse::permissionDenied();
    }

    private function checkOwns(User $user, Request $request, string $args): ?JsonResponse
    {
        $parts = explode(':', $args, 2);
        $modelName = $parts[0] ?? '';
        $paramName = $parts[1] ?? '';

        if (! $modelName || ! $paramName) {
            return ApiResponse::error('Invalid resource access rule.', 500);
        }

        $value = $this->resolveParam($request, $paramName);
        if (is_null($value)) {
            return ApiResponse::permissionDenied();
        }

        $modelClass = 'App\\Models\\' . $modelName;
        if (! class_exists($modelClass)) {
            return ApiResponse::error('Unknown resource model.', 500);
        }

        $model = $modelClass::find((int) $value);
        if (! $model) {
            return ApiResponse::error('Resource not found.', 404);
        }

        if ($user->hasRole('owner')) {
            $ownedClinicId = $user->clinicOwner?->id;
            if (! $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }
            $clinicId = $this->resolveClinicId($model);
            if (! $clinicId || (int) $clinicId !== $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('doctor')) {
            $doctor = $user->doctorProfile;
            if (! $doctor) {
                return ApiResponse::permissionDenied();
            }

            if ($model instanceof Secretary) {
                if ((int) $model->clinic_id !== (int) $doctor->clinic_id) {
                    return ApiResponse::permissionDenied();
                }

                return null;
            }

            $doctorId = $doctor->id;
            $doctorField = $model->doctor_id ?? $model->appointment?->doctor_id;
            if (! $doctorField || (int) $doctorField !== $doctorId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('patient')) {
            $patientId = $user->patientProfile?->id;
            if (! $patientId) {
                return ApiResponse::permissionDenied();
            }
            if (! isset($model->patient_id) || (int) $model->patient_id !== $patientId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        if ($user->hasRole('secretary')) {
            $secretary = $user->secretaryProfile;
            if (! $secretary) {
                return ApiResponse::permissionDenied();
            }

            if ($model instanceof Secretary) {
                if ((int) $model->id === (int) $secretary->id || (int) $model->clinic_id === (int) $secretary->clinic_id) {
                    return null;
                }

                return ApiResponse::permissionDenied();
            }

            $roomId = $model->room_id ?? $model->appointment?->room_id;
            if (! $roomId) {
                return ApiResponse::permissionDenied();
            }
            $assignedIds = $secretary->rooms()->pluck('rooms.id')->toArray();
            if (! in_array((int) $roomId, $assignedIds)) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        return ApiResponse::permissionDenied();
    }

    private function resolveClinicId($model): ?int
    {
        if (isset($model->clinic_id) && $model->clinic_id !== null) {
            return (int) $model->clinic_id;
        }

        return match (true) {
            $model instanceof Payment => $model->invoice?->clinic_id,
            $model instanceof Work_hour => $model->doctor?->clinic_id,
            $model instanceof Schedule_override => $model->doctor?->clinic_id,
            $model instanceof PatientInfo => $model->appointments()->first()?->clinic_id,
            default => null,
        };
    }

    private function checkOwnerClinic(User $user, Request $request, string $args): ?JsonResponse
    {
        if (! $user->hasRole('owner')) {
            return ApiResponse::permissionDenied();
        }

        $ownedClinicId = $user->clinicOwner?->id;
        if (! $ownedClinicId) {
            return ApiResponse::permissionDenied();
        }

        $parts = explode(':', $args, 2);
        $first = $parts[0] ?? '';
        $second = $parts[1] ?? '';

        if ($second === '') {
            $value = $this->resolveParam($request, $first);
            if (is_null($value)) {
                return ApiResponse::permissionDenied();
            }
            if ((int) $value !== $ownedClinicId) {
                return ApiResponse::permissionDenied();
            }

            return null;
        }

        $modelClass = 'App\\Models\\' . $first;
        $paramName = $second;

        if (! class_exists($modelClass)) {
            return ApiResponse::error('Unknown resource model.', 500);
        }

        $value = $this->resolveParam($request, $paramName);
        if (is_null($value)) {
            return ApiResponse::permissionDenied();
        }

        $model = $modelClass::find((int) $value);
        if (! $model) {
            return ApiResponse::error('Resource not found.', 404);
        }

        $clinicId = $this->resolveClinicId($model);
        if (! $clinicId || (int) $clinicId !== $ownedClinicId) {
            return ApiResponse::permissionDenied();
        }

        return null;
    }
}
