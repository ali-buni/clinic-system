<?php

namespace App\Http\Controllers;

use App\Actions\Doctor\CreateDoctorAction;
use App\Actions\Doctor\UpdateDoctorAction;
use App\Actions\Doctor\DeleteDoctorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
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

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }
    public function store(StoreDoctorRequest $request, CreateDoctorAction $action): JsonResponse
    {
        $owner = $request->user();

        if (!$owner->clinicOwner?->id) {
            return response()->json([
                'message' => 'The current account is not associated with any clinic.'
            ], 400);
        }

        try {
            $doctor = $action->execute(
                $request->validated(),
                $owner->clinicOwner->id
            );

            return response()->json([
                'message' => 'Doctor created successfully.',
                'doctor'  => new DoctorResource($doctor->load('user'))
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create doctor', [
                'user_id'=> $owner->id,
                'error'=> $e->getMessage()
            ]);

            return response()->json([
                'message'=> 'Failed to create doctor.',
                'error'=> config('app.debug') ? $e->getMessage() : null,], 500);
        }
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor, UpdateDoctorAction $action): JsonResponse
    {
        Gate::authorize('update', $doctor);

        try {
            $updatedDoctor = $action->execute(
                $doctor,
                $request->validated()
            );

            return response()->json([
                'message' => 'Your profile has been updated successfully.',
                'doctor'  => new DoctorResource($updatedDoctor->load('user'))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update doctor', [
                'doctor_id' => $doctor->id,
                'error'     => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to update doctor.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function destroy(Doctor $doctor, DeleteDoctorAction $action): JsonResponse
    {
        Gate::authorize('delete', $doctor);

        try {
            $action->execute($doctor);

            return response()->json([
             'message' => 'The doctor has successfully left the clinic.'
            ], 200);

        } catch (\Exception $e) {

            Log::error('Failed to remove doctor', [
                'doctor_id' => $doctor->id,
                'user_id'   => request()->user()->id,
                'error'     => $e->getMessage(),
                'trace'     => config('app.debug') ? $e->getTraceAsString() : null
            ]);

            $status = $e->getCode() == 400 ? 400 : 500;

            return response()->json([
                'message' => $e->getMessage() ?: 'Failed to remove doctor.',
            ], $status);
        }
    }

    public function restore(Doctor $doctor): JsonResponse
    {
        Gate::authorize('restore', $doctor);

        $doctor->restore();

        return response()->json([
            'message' => 'Doctor restored successfully.',
            'doctor'  => new DoctorResource($doctor->load('user'))
        ]);
    }

    public function show(Doctor $doctor): JsonResponse
    {

        return response()->json([
            'doctor'=> new DoctorResource($doctor->load('user'))
        ], 200);
    }

    public function roomDoctors(Request $request, Room $room): JsonResponse
    {
        $owner = $request->user();
        $clinicId = $owner->clinicOwner?->id;

        if ($room->clinic_id !== $clinicId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $doctors = Doctor::where('room_id', $room->id)
            ->with(['user', 'specialties'])
            ->get();

        return response()->json([
            'room' => $room->only(['id', 'name', 'number']),
            'doctors' => DoctorResource::collection($doctors)
        ]);
    }
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->clinicOwner?->id) {
            $clinicId = $user->clinicOwner->id;

        $query = Doctor::where('clinic_id', $clinicId)
            ->with(['user', 'specialties', 'room'])
            ->withTrashed(); // اختياري: إظهار المحذوفين

        $result = ModelFilter::filter($query, $request->all());

        return response()->json([
            'doctors' => DoctorResource::collection($result->items()),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'total'        => $result->total(),
                'per_page'     => $result->perPage(),
            ]
        ], 200);
        }

        $doctor = $user->doctor;

        if ($doctor) {
        return response()->json([
            'doctor' => new DoctorResource($doctor->load(['user', 'specialties', 'room']))
        ], 200);
        }

        return response()->json([
        'message' => 'Not associated with any clinic.'
        ], 403);
    }
    public function forceDelete(Doctor $doctor): JsonResponse
    {
        Gate::authorize('forceDelete', $doctor);

        try {
            $doctor->forceDelete();

        return response()->json([
            'message' => 'The doctor has been permanently deleted from the system.'
        ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to force delete doctor', [
            'doctor_id' => $doctor->id,
            'user_id'   => request()->user()->id,
            'error'     => $e->getMessage()
            ]);

        return response()->json([
            'message' => 'Failed to permanently delete doctor.',
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
        }
    }
}
