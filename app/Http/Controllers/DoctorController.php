<?php

namespace App\Http\Controllers;

use App\Actions\Doctor\UpdateDoctorAction;
use App\Actions\Doctor\DeleteDoctorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Services\ModelFilter;
use App\Services\ApiResponse;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function info($id): JsonResponse
    {
        $Doctor = Doctor::query()
            ->with(['user', 'room'])
            ->find($id);

        if (!$Doctor) {
            return ApiResponse::error('Doctor not found', 404);
        }
        return ApiResponse::success(new DoctorResource($Doctor));
    }

    public function update(UpdateDoctorRequest $request, UpdateDoctorAction $action): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('no user found.');
        }
        try {
            $updatedDoctor = $action->execute(
                $user->doctorProfile?->id,
                $request->validated()
            );
            if (!$updatedDoctor) ApiResponse::error('the doctor in not updated');
            return ApiResponse::success(null, 'Your profile has been updated successfully.', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update doctor.',
                500,
                ['error' => $e->getMessage() ?? null]
            );
        }
    }

    public function destroy(Doctor $doctor, DeleteDoctorAction $action): JsonResponse
    {
        try {
            $action->execute($doctor);

            return ApiResponse::success(
                null,
                'The doctor has successfully left the clinic.',
                200
            );
        } catch (\Exception $e) {
            Log::error('Failed to remove doctor', [
                'doctor_id' => $doctor->id,
                'user_id'   => request()->user()->id,
                'error'     => $e->getMessage(),
                'trace'     => config('app.debug') ? $e->getTraceAsString() : null
            ]);

            $status = $e->getCode() == 400 ? 400 : 500;

            return ApiResponse::error(
                $e->getMessage() ?: 'Failed to remove doctor.',
                $status
            );
        }
    }

    public function restore(Doctor $doctor): JsonResponse
    {
        $doctor->restore();

        return ApiResponse::success(
            new DoctorResource($doctor->load('user')),
            'Doctor restored successfully.',
            200
        );
    }

    public function index(FilterRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        if ($user->clinicOwner?->id) {
            $clinicId = $user->clinicOwner->id;

            $query = Doctor::where('clinic_id', $clinicId)
                ->with(['user', 'specialties'])
                ->withTrashed();

            $result = ModelFilter::filter($query, $validated);


            return ApiResponse::pagination(
                $result,
                'Doctors collection retrieved successfully.',
                DoctorResource::collection($result)
            );
        }
        return ApiResponse::permissionDenied('Not associated with any clinic.', 403);
    }

    public function forceDelete(Doctor $doctor): JsonResponse
    {
        try {
            $doctor->forceDelete();

            return ApiResponse::success(
                null,
                'The doctor has been permanently deleted from the system.',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to permanently delete doctor.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }
}
