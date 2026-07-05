<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\Secretary\SecretaryRequest;
use App\Http\Resources\Secretary\SecretaryResource;
use App\Models\Secretary;
use App\Services\ApiResponse;
use App\Services\ModelFilter;
use App\Services\SecretaryService;
use Illuminate\Support\Facades\Auth;

class SecretaryController extends Controller
{
    public function __construct(private SecretaryService $secretary_service) {}

    public function index(FilterRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        if ($user->clinicOwner?->id || $user->secretaryProfile?->clinic_id) {
            $clinicId = $user->clinicOwner?->id ?? $user->secretaryProfile?->clinic_id;

            $query = Secretary::where('clinic_id', $clinicId)
                ->with(['user', 'rooms'])
                ->withTrashed();

            $result = ModelFilter::filter($query, $validated);

            return ApiResponse::pagination(
                $result,
                'Secretaries collection retrieved successfully.',
                SecretaryResource::collection($result)
            );
        }
        return ApiResponse::permissionDenied('Not associated with any clinic.', 403);
    }

    public function info($id)
    {
        $secretary = $this->secretary_service->info($id);

        if (!$secretary) {
            return ApiResponse::error('Secretary not found', 404);
        }
        return ApiResponse::success(new SecretaryResource($secretary));
    }

    public function update(SecretaryRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('no user found.');
        }
        $secretary = $this->secretary_service->update($user->secretaryProfile->id, $data);

        if (! $secretary) {
            return ApiResponse::error('Secretary not found', 404);
        }
        return ApiResponse::success();
    }

    public function destroy(Secretary $secretary)
    {
        $secretary->delete();

        return ApiResponse::success(null, 'Secretary removed successfully.', 200);
    }

    public function restore(Secretary $secretary)
    {
        $secretary->restore();

        return ApiResponse::success(
            new SecretaryResource($secretary->load('user')),
            'Secretary restored successfully.',
            200
        );
    }
}
