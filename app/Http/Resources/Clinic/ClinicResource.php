<?php

namespace App\Http\Resources\Clinic;

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
            'country' => $this->whenLoaded('location', fn() => $this->location->country),
            'governorate' => $this->whenLoaded('location', fn() => $this->location->governorate),
            'city' => $this->whenLoaded('location', fn() => $this->location->city),
            'location_name' => $this->whenLoaded('location', fn() => $this->location->name),
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

            'doctors' => $this->doctors->loadMissing(['specialties', 'user'])->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->fname . ' ' . $doctor->user->lname,
                    'specialities' => $doctor->specialties->map(function ($specialty) {
                        return $specialty->only(['ar_name', 'en_name']);
                    })->values(),
                    'phone' => $doctor->user->phone,
                    'room_id' => $doctor->room_id,
                ];
            }),
            'secretaries' => $this->secretaries->loadMissing('user')->map(function ($secretary) {
                return [
                    'id' => $secretary->id,
                    'name' => $secretary->user->fname . ' ' . $secretary->user->lname,
                    'phone' => $secretary->user->phone,
                    'room_ids' => $secretary->rooms->pluck('id'),
                ];
            }),
        ];
    }
}
