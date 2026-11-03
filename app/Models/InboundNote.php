<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboundNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'supplier_id',
        'user_id',
        'created_date',
        'total_amount',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InboundDetail::class);
    }
}
