<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicResource extends JsonResource
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
            'title' => $this->title,
            'location' => $this->location,
            'phone' => $this->phone,
            'rooms_count' => $this->whenLoaded('rooms', function () {
                return [
                    'total' => $this->rooms->count(),
                    'details' => $this->rooms->map(function ($room) {
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,

            'doctors' => $this->doctors->map(function ($doctor) {
                return [
                    'name' => $doctor->user->fname . ' ' . $doctor->user->lname,
                    'specialities' => $doctor->specialties->map(function ($specialty) {
                        return $specialty->only(['ar_name', 'en_name']);
                    })->values(),
                    'phone' => $doctor->user->phone,
                    'room_id' => $doctor->room_id,
                ];
            }),
            'secretaries' => $this->secretaries->map(function ($secretary) {
                return [
                    'name' => $secretary->user->fname . ' ' . $secretary->user->lname,
                    'phone' => $secretary->user->phone,
                    'room_ids' => $secretary->whenLoaded('rooms', fn() => $secretary->rooms->pluck('id')),
                ];
            }),
        ];
    }
}
