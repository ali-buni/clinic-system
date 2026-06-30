<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRoomsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'clinic_id' => $this->clinic_id,
            'created' => $this->created_at->format('Y-m-d'),
            'doctors' => $this->doctors->loadMissing('user')->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->fname . ' ' . $doctor->user->lname,
                ];
            }),
            'secretaries' => $this->secretaries->loadMissing('user')->map(function ($secretary) {
                return [
                    'id' => $secretary->id,
                    'name' => $secretary->user->fname . ' ' . $secretary->user->lname,
                ];
            }),
        ];
    }
}
