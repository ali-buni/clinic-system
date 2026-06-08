<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $route = request()->route();
        $action = $route->getActionMethod(); // 'index', 'show', 'details'

        return [
            'id' => $this->id,
            'clinic_id' => $this->clinic_id,
            'name' => $this->name,
            'created' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'doctors' => $this->doctors->map(function ($doctor) use ($action) {
                $data = [
                    'id' => $doctor->id,
                    'name' => $doctor->user->fname . ' ' . $doctor->user->lname,
                ];
                if ($action === 'get') {
                    $data['phone'] = $doctor->user->phone;
                    $data['created'] = $doctor->created_at ? $doctor->created_at->format('Y-m-d') : null;
                    $data['gender'] = $doctor->user->gender;
                    $data['bio'] = $doctor->bio;
                    $data['specialties'] = $doctor->specialties->pluck('name');
                }

                return $data;
            }),
            'secretaries' => $this->secretaries->map(function ($secretaries) use ($action) {
                $data = [
                    'id' => $secretaries->id,
                    'name' => $secretaries->user->fname . ' ' . $secretaries->user->lname,
                ];
                if ($action === 'get') {
                    $data['phone'] = $secretaries->user->phone;
                    $data['created'] = $secretaries->created_at ? $secretaries->created_at->format('Y-m-d') : null;
                    $data['gender'] = $secretaries->user->gender;
                }

                return $data;
            }),
        ];
    }
}
