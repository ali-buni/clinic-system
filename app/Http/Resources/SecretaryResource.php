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
        $data = [];

        $this->whenLoaded('user', function () use (&$data) {
            $data['name'] = $this->user->fname . ' ' . $this->user->lname;
            $data['phone'] = $this->user->phone;
            $data['dob'] = Carbon::parse($this->user->dob)->format('Y-m-d');
            $data['gender'] = $this->user->gender;
        });
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'created_at' => $this->created_at->format('Y-m-d'),
            'role' => 'secretary',
            ...$data,
        ];
    }
}
