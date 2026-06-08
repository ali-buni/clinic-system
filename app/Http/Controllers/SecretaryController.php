<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecretaryRequest;
use App\Http\Resources\SecretaryResource;
use App\Models\Room;
use App\Services\ApiResponse;
use App\Services\SecretaryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class SecretaryController extends Controller
{
    public function __construct(private SecretaryService $secretary_service) {}

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
}
