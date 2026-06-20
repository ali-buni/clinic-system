<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'created_at' => $this->created_at->format('Y-m-d'),
            'role' => 'secretary',
        ], $this->whenLoaded('user', function () {
            return [
                'name' => $this->user->fname . ' ' . $this->user->lname,
                'phone' => $this->user->phone,
                'dob' => Carbon::parse($this->user->dob)->format('Y-m-d'),
                'gender' => $this->user->gender,
            ];
        }));
    }
}
