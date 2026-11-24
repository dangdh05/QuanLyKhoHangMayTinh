<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'transaction_type', // 'inbound', 'outbound', 'adjust', 'cancel_inbound', 'cancel_outbound'
        'ref_code',         // PN001, PX001, DC001...
        'change_quantity',  // e.g. +10, -4, -1
        'balance_quantity', // Tồn sau giao dịch
        'note',
    ];

    protected function casts(): array
    {
        return [
            'change_quantity' => 'integer',
            'balance_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
