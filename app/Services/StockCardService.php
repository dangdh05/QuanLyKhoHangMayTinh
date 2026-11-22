<?php

namespace App\Services;

use App\Models\StockCard;

class StockCardService
{
    /**
     * Ghi biến động vào thẻ kho theo đúng QT4
     */
    public function ghiTheKho(int $productId, string $transactionType, string $refCode, int $changeQuantity, int $balanceQuantity, ?string $note = null): StockCard
    {
        return StockCard::create([
            'product_id' => $productId,
            'transaction_type' => $transactionType,
            'ref_code' => $refCode,
            'change_quantity' => $changeQuantity,
            'balance_quantity' => $balanceQuantity,
            'note' => $note,
        ]);
    }

    /**
     * Lấy danh sách thẻ kho của một mặt hàng
     */
    public function layTheKho(int $productId, int $perPage = 25)
    {
        return StockCard::where('product_id', $productId)
            ->with('product')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
