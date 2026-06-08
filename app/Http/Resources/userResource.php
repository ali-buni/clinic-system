<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class userResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $data = [];
        if ($user->hasRole('doctor')) {
            $this->doctors->map(function ($doctor) use (&$data) {
                return [
                    $data['specialities'] = $doctor->specialities()->pluck('name'),
                    $data['appointment_duration'] = $doctor->appointment_duration,
                    $data['bio'] = $doctor->bio,
                    $data['consultation_fee'] = $doctor->consultation_fee,
                ];
            });
        }
        return [
            'id' => $this->id,
            'name' => $this->fname . ' ' . $this->lname,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'dob' => Carbon::parse($this->dob)->format('Y-m-d'),
            'created' => $this->created_at->format('Y-m-d'),
            'role' => $user->hasRole('doctor') ? 'doctor' : 'secretary',
            ...$data,
        ];
    }
}
