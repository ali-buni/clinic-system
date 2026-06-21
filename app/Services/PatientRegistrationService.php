<?php

namespace App\Services;

use App\Models\PatientInfo;
use App\Models\User;
use App\Traits\HandleUserImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientRegistrationService
{
    use HandleUserImage;

    public function __construct(
        private ApiResponse $apiResponse,
        private VerificationService $verification,
    ) {}

    public function register(array $data): \Illuminate\Http\JsonResponse
    {
        return DB::transaction(function () use ($data) {
            $image = $data['profile_image'] ?? null;
            $profileImage = $this->handleUserImage($image);

            $user = User::create([
                'fname'         => $data['fname'],
                'lname'         => $data['lname'],
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'dob'           => $data['dob'] ?? null,
                'gender'        => $data['gender'] ?? 'unknown',
                'profile_image' => $profileImage,
            ]);

            $user->assignRole('patient');

            PatientInfo::create([
                'user_id'            => $user->id,
                'clinic_id'          => $data['clinic_id'] ?? 1,
                'nationality'        => $data['nationality'] ?? null,
                'address'            => $data['address'] ?? null,
                'marital_status'     => $data['marital_status'] ?? null,
                'emergency_phone'    => $data['emergency_phone'] ?? null,
                'allergies'          => $data['allergies'] ?? null,
                'chronic_conditions' => $data['chronic_conditions'] ?? null,
                'career'             => $data['career'] ?? null,
                'blood_type'         => $data['blood_type'] ?? null,
            ]);

            $this->verification->sendVerificationCode($user, 'email');

            return $this->apiResponse->success(
                null,
                'Registration successful. Please check your email for the verification code.'
            );
        }, attempts: 3);
    }
}
