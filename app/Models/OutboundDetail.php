<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_note_id',
        'product_id',
        'quantity',
        'export_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'export_price' => 'decimal:2',
        ];
    }

    public function outboundNote(): BelongsTo
    {
        return $this->belongsTo(OutboundNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * $this->export_price);
    }
}
