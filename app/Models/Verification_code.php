<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification_code extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'sent_to',
        'code_hash',
        'attempts',
        'last_sent_at',
        'expires_at',
        'consumed_at'
    ];

    protected $casts = [
        'last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
