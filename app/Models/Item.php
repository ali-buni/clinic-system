<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Item extends Model
{
    use hasFactory;

    protected $fillable = [
        'item_name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items', 'item_id', 'invoice_id')
            ->withPivot('item_name', 'price', 'quantity')
            ->withTimestamps();
    }
}
