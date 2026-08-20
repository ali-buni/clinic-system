<?php

namespace App\Http\Resources\Secretary;

use App\Helpers\ResourceSecurityHelper;
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
        $requester = $request->user();
        $canViewFull = $requester
            && ((int) $requester->clinicOwner?->id === (int) $this->clinic_id
                || (int) $requester->id === (int) $this->user_id);

        return array_merge([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'created_at' => $this->created_at->format('Y-m-d'),
            'role' => 'secretary',
        ], $this->whenLoaded('user', function () use ($requester, $canViewFull) {
            return [
                'name' => $this->user->fname.' '.$this->user->lname,
                'email' => ResourceSecurityHelper::maskEmail($this->user->email, $requester, $this->user_id, $canViewFull),
                'phone' => ResourceSecurityHelper::maskPhone($this->user->phone, $requester, $this->user_id, $canViewFull),
                'dob' => $canViewFull ? Carbon::parse($this->user->dob)->format('Y-m-d') : null,
                'gender' => $this->user->gender,
            ];
        }), $this->whenLoaded('rooms', function () {
            return [
                'rooms' => $this->rooms->map(fn ($room) => [
                    'id' => $room->id,
                    'name' => $room->name,
                ]),
            ];
        }));
    }
}
