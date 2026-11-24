<?php

namespace App\Services;

use App\Models\InboundNote;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    public function layDanhSach(array $filters = [])
    {
        $query = Supplier::query()->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        return $query->paginate(15);
    }

    public function luuNhaCungCap(array $data, ?int $id = null): Supplier
    {
        $codeCheck = Supplier::where('code', $data['code']);
        if ($id) {
            $codeCheck->where('id', '!=', $id);
        }
        if ($codeCheck->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Mã nhà cung cấp đã tồn tại.',
            ]);
        }

        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $supplier->update($data);

            return $supplier;
        }

        return Supplier::create($data);
    }

    public function xoaNhaCungCap(int $id): bool
    {
        $supplier = Supplier::findOrFail($id);

        $hasInbounds = InboundNote::where('supplier_id', $id)->exists();

        if ($hasInbounds) {
            // Đã có phiếu nhập -> Ngừng hoạt động
            $supplier->status = false;
            $supplier->save();

            return false;
        }

        $supplier->delete();

        return true;
    }
}
