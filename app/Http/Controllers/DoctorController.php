<?php

namespace App\Http\Controllers;

use App\Actions\Doctor\UpdateDoctorAction;
use App\Actions\Doctor\DeleteDoctorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use App\Services\ModelFilter;
use App\Services\ApiResponse;

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor, UpdateDoctorAction $action): JsonResponse
    {
        Gate::authorize('update', $doctor);

        try {
            $updatedDoctor = $action->execute(
                $doctor,
                $request->validated()
            );


            return ApiResponse::success(
                new DoctorResource($updatedDoctor->load('user')),
                'Your profile has been updated successfully.',
                200
            );

        } catch (\Exception $e) {
            Log::error('Failed to update doctor', [
                'doctor_id' => $doctor->id,
                'error'     => $e->getMessage()
            ]);


            return ApiResponse::error(
                'Failed to update doctor.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }

    public function destroy(Doctor $doctor, DeleteDoctorAction $action): JsonResponse
    {
        Gate::authorize('delete', $doctor);

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
        Gate::authorize('restore', $doctor);

        $doctor->restore();

        return ApiResponse::success(
            new DoctorResource($doctor->load('user')),
            'Doctor restored successfully.',
            200
        );
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return ApiResponse::success(
            new DoctorResource($doctor->load('user')),
            'Doctor details retrieved successfully.',
            200
        );
    }

    public function roomDoctors(Request $request, Room $room): JsonResponse
    {
        $owner = $request->user();
        $clinicId = $owner->clinicOwner?->id;

        if ($room->clinic_id !== $clinicId) {

            return ApiResponse::permissionDenied('Unauthorized', 403);
        }

        $doctors = Doctor::where('room_id', $room->id)
            ->with(['user'])
            ->select('id', 'user_id')
            ->get();

        $doctorsData = $doctors->map(function ($doctor) {
            $user = $doctor->user;
            return [
                'id'        => $doctor->id,
                'full_name' => trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: null,
            ];
        });

        return ApiResponse::success([
            'room' => $room->only(['id', 'name', 'number']),
            'doctors' => $doctorsData
        ], 'Room doctors retrieved successfully.', 200);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->clinicOwner?->id) {
            $clinicId = $user->clinicOwner->id;

            $query = Doctor::where('clinic_id', $clinicId)
                ->with(['user', 'specialties', 'room'])
                ->withTrashed();

            $result = ModelFilter::filter($query, $request->all());


            return ApiResponse::pagination(
                $result,
                'Doctors collection retrieved successfully.'
            );
        }

        $doctor = $user->doctor;

        if ($doctor) {
            return ApiResponse::success(
                new DoctorResource($doctor->load(['user', 'specialties', 'room'])),
                'Doctor profile retrieved successfully.',
                200
            );
        }

        return ApiResponse::permissionDenied('Not associated with any clinic.', 403);
    }

    public function forceDelete(Doctor $doctor): JsonResponse
    {
        Gate::authorize('forceDelete', $doctor);

        try {
            $doctor->forceDelete();

            return ApiResponse::success(
                null,
                'The doctor has been permanently deleted from the system.',
                200
            );

        } catch (\Exception $e) {
            Log::error('Failed to force delete doctor', [
                'doctor_id' => $doctor->id,
                'user_id'   => request()->user()->id,
                'error'     => $e->getMessage()
            ]);

            return ApiResponse::error(
                'Failed to permanently delete doctor.',
                500,
                config('app.debug') ? ['error' => $e->getMessage()] : null
            );
        }
    }
}
