<?php

namespace App\Services;

use App\Models\PatientInfo;
use App\Models\User;
use App\Traits\HandleUserImage;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientRegistrationService
{
    use HandleUserImage;

    public function __construct(
        private ApiResponse $apiResponse,
        private VerificationService $verification,
    ) {}

    public function register(array $data): JsonResponse
    {
        if (User::where('email_hash', User::hashEmail($data['email']))->exists()) {
            return $this->apiResponse->error('This email is already registered.', 422);
        }

        return DB::transaction(function () use ($data) {
            $image = $data['profile_image'] ?? null;
            $profileImage = $this->handleUserImage($image);

            try {
                $user = User::create([
                    'fname' => $data['fname'],
                    'lname' => $data['lname'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'dob' => $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? 'unknown',
                    'profile_image' => $profileImage,
                ]);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return $this->apiResponse->error('This email is already registered.', 422);
                }
                throw $e;
            }

            $user->assignRole('patient');

            PatientInfo::create([
                'user_id' => $user->id,
                'clinic_id' => $data['clinic_id'] ?? 1,
                'nationality' => $data['nationality'] ?? null,
                'address' => $data['address'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'emergency_phone' => $data['emergency_phone'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'chronic_conditions' => $data['chronic_conditions'] ?? null,
                'career' => $data['career'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
            ]);

            $this->verification->sendVerificationCode($user, 'email');

            return $this->apiResponse->success(
                null,
                'Registration successful. Please check your email for the verification code.'
            );
        }, attempts: 3);
    }
}
