<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment_method extends Model
{
    use HasFactory;

    protected $fillable = ['ar_name', 'en_name', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'type' => PaymentMethodType::class,
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'id');
    }
}
