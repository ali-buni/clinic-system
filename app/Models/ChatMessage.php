<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chattable_type',
        'chattable_id',
        'message',
        'response',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chattable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function GenerateSessionId(int $patientInfoId, int $userId): string
    {
        return "patient_{$patientInfoId}_user_{$userId}_" . now()->timestamp;
    }
}
