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
        $ownerId = $this->user_id;

        return array_merge([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'created_at' => $this->created_at->format('Y-m-d'),
            'role' => 'secretary',
        ], $this->whenLoaded('user', function () use ($requester, $ownerId) {
            return [
                'name' => $this->user->fname . ' ' . $this->user->lname,
                'email' => ResourceSecurityHelper::maskEmail($this->user->email, $requester, $ownerId),
                'phone' => $this->user->phone,
                'dob' => Carbon::parse($this->user->dob)->format('Y-m-d'),
                'gender' => $this->user->gender,
            ];
        }));
    }
}
