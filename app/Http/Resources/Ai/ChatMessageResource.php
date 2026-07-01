<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'response' => $this->response,
            'session_id' => $this->session_id,
            'created_at' => $this->created_at,
        ];
    }
}
