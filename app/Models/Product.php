<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand',
        'cpu',
        'ram',
        'storage',
        'gpu',
        'release_date',
        'cost_price',
        'selling_price',
        'quantity',
        'min_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity' => 'integer',
            'min_quantity' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Số tháng kể từ ngày mở bán/ra mắt đến hiện tại
     */
    public function getMonthsSinceReleaseAttribute(): ?int
    {
        if (! $this->release_date) {
            return null;
        }

        return (int) round($this->release_date->diffInMonths(now()));
    }

    /**
     * Kiểm tra hàng tồn kho ứ đọng/lâu năm theo ngày mở bán
     */
    public function isAgingOverstock(int $thresholdMonths = 12): bool
    {
        if (! $this->release_date || $this->quantity <= 0) {
            return false;
        }

        return $this->months_since_release >= $thresholdMonths;
    }

    /**
     * Mức độ cảnh báo rủi ro tồn kho ứ đọng theo thời gian mở bán
     */
    public function getAgingRiskLevelAttribute(): string
    {
        if (! $this->release_date || $this->quantity <= 0) {
            return 'normal';
        }

        $months = $this->months_since_release;

        if ($months >= 12) {
            return 'danger'; // Báo động đỏ: Mở bán > 12 tháng vẫn tồn nhiều máy
        }

        if ($months >= 6) {
            return 'warning'; // Cần lưu ý: Mở bán > 6 tháng
        }

        return 'normal';
    }

    /**
     * Nhãn cảnh báo rủi ro tồn kho ứ đọng
     */
    public function getAgingRiskLabelAttribute(): string
    {
        $level = $this->aging_risk_level;

        return match ($level) {
            'danger' => 'Báo động ứ đọng (>12 tháng)',
            'warning' => 'Cần lưu ý (>6 tháng)',
            default => 'Bình thường (<6 tháng)',
        };
    }

    public function getInventoryValueAttribute(): float
    {
        return (float) ($this->quantity * $this->cost_price);
    }

    public function inboundDetails(): HasMany
    {
        return $this->hasMany(InboundDetail::class);
    }

    public function outboundDetails(): HasMany
    {
        return $this->hasMany(OutboundDetail::class);
    }

    public function stockCards(): HasMany
    {
        return $this->hasMany(StockCard::class)->orderBy('id', 'desc');
    }
}
