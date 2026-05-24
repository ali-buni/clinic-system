<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment_method extends Model
{
    protected $fillable = ['ar_name', 'en_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'id');
    }
}
