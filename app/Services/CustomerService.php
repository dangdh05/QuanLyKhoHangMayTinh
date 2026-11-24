<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\OutboundNote;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function layDanhSach(array $filters = [])
    {
        $query = Customer::query()->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%")
                    ->orWhere('address', 'like', "%{$kw}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        return $query->paginate(15);
    }

    public function luuKhachHang(array $data, ?int $id = null): Customer
    {
        $codeCheck = Customer::where('code', $data['code']);
        if ($id) {
            $codeCheck->where('id', '!=', $id);
        }
        if ($codeCheck->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Mã khách hàng đã tồn tại.',
            ]);
        }

        if ($id) {
            $customer = Customer::findOrFail($id);
            $customer->update($data);

            return $customer;
        }

        return Customer::create($data);
    }

    public function xoaKhachHang(int $id): bool
    {
        $customer = Customer::findOrFail($id);

        $hasOutbounds = OutboundNote::where('customer_id', $id)->exists();

        if ($hasOutbounds) {
            $customer->status = false;
            $customer->save();

            return false;
        }

        $customer->delete();

        return true;
    }
}
