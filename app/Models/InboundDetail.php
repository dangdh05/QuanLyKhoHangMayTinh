<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_note_id',
        'product_id',
        'quantity',
        'import_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'import_price' => 'decimal:2',
        ];
    }

    public function inboundNote(): BelongsTo
    {
        return $this->belongsTo(InboundNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * $this->import_price);
    }
}
