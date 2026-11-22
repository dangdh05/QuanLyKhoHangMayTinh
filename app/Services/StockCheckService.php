<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockCheckService
{
    protected StockCardService $stockCardService;

    public function __construct(StockCardService $stockCardService)
    {
        $this->stockCardService = $stockCardService;
    }

    /**
     * Thực hiện kiểm kê và điều chỉnh tồn kho
     */
    public function dieuChinhTon(array $adjustments, int $userId): array
    {
        // 1. Kiểm tra validation: Nếu có chênh lệch thì bắt buộc phải có lý do
        $itemsToAdjust = [];
        foreach ($adjustments as $idx => $item) {
            $productId = (int) $item['product_id'];
            $actualQty = (int) $item['actual_quantity'];
            $reason = trim($item['reason'] ?? '');

            $product = Product::findOrFail($productId);
            $diff = $actualQty - $product->quantity;

            if ($diff !== 0) {
                if (empty($reason)) {
                    throw ValidationException::withMessages([
                        "adjustments.{$idx}.reason" => "Mặt hàng [{$product->code}] có chênh lệch ({$diff}), bắt buộc phải nhập lý do điều chỉnh.",
                    ]);
                }

                $itemsToAdjust[] = [
                    'product' => $product,
                    'actual_quantity' => $actualQty,
                    'diff' => $diff,
                    'reason' => $reason,
                ];
            }
        }

        if (empty($itemsToAdjust)) {
            return [
                'adjusted_count' => 0,
                'message' => 'Số lượng thực tế khớp hoàn toàn với số lượng sổ sách, không có chênh lệch cần điều chỉnh.',
            ];
        }

        // 2. Database Transaction (QT3)
        DB::transaction(function () use ($itemsToAdjust) {
            $refCode = 'DC'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

            foreach ($itemsToAdjust as $item) {
                /** @var Product $product */
                $product = Product::lockForUpdate()->find($item['product']->id);
                $diff = $item['diff'];
                $newQty = $item['actual_quantity'];

                // Cập nhật tồn thực tế
                $product->quantity = $newQty;
                $product->save();

                // Ghi thẻ kho (QT4)
                $this->stockCardService->ghiTheKho(
                    productId: $product->id,
                    transactionType: 'adjust',
                    refCode: $refCode,
                    changeQuantity: $diff,
                    balanceQuantity: $newQty,
                    note: 'Kiểm kê kho: '.$item['reason']
                );
            }
        });

        return [
            'adjusted_count' => count($itemsToAdjust),
            'message' => 'Đã điều chỉnh tồn kho thành công cho '.count($itemsToAdjust).' mặt hàng.',
        ];
    }
}
