@extends('layouts.app')

@section('title', 'Lập Phiếu Nhập')
@section('page_title', 'Lập Phiếu Nhập Kho')

@section('content')
<form action="{{ route('inbounds.store') }}" method="POST" id="inboundForm">
    @csrf
    <div class="row g-3">
        <!-- Thông tin chung -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-2 px-3">
                    <span class="fw-semibold">Thông Tin Phiếu</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Nhà cung cấp * (QT1)</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    [{{ $sup->code }}] {{ $sup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-medium">Ngày lập</label>
                        <input type="datetime-local" name="created_date" class="form-control" value="{{ old('created_date', now()->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-medium">Người lập</label>
                        <input type="text" class="form-control bg-light" value="{{ Auth::user()->full_name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Số hóa đơn, hợp đồng...">{{ old('note') }}</textarea>
                    </div>

                    <div class="p-3 bg-light rounded border-0">
                        <div class="text-muted small">Tổng tiền phiếu:</div>
                        <h4 class="fw-bold text-primary money-val mb-0 text-nowrap" id="displayTotal">0&nbsp;đ</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi tiết mặt hàng -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="fw-semibold">Danh Sách Máy Nhập</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                        <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle" id="tableItems">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Mặt hàng máy tính *</th>
                                    <th style="width: 20%;">Số lượng *</th>
                                    <th style="width: 25%;">Đơn giá nhập (VNĐ) *</th>
                                    <th style="width: 10%;" class="text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Dynamic rows inserted by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('inbounds.index') }}" class="btn btn-sm btn-light border">Hủy</a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2 me-1"></i>Lưu & Cập nhật tồn kho (QT3)
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][product_id]" class="form-select product-select" required onchange="calculateSubtotal(this)">
                <option value="">-- Chọn máy tính --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" data-qty="{{ $p->quantity }}">
                        [{{ $p->code }}] {{ $p->name }} (Tồn: {{ $p->quantity }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control item-qty" min="1" value="1" required oninput="calculateSubtotal(this)">
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][import_price]" class="form-control item-price" min="1" placeholder="Đơn giá..." required oninput="calculateSubtotal(this)">
            <div class="text-end small text-muted money-val subtotal-text mt-1 text-nowrap">0&nbsp;đ</div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2 btn-remove-row" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    let rowIndex = 0;

    function addRow() {
        const template = document.getElementById('rowTemplate').innerHTML;
        const newRowHtml = template.replace(/INDEX/g, rowIndex);
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', newRowHtml);
        rowIndex++;
        calculateTotal();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('Phiếu nhập phải có ít nhất một mặt hàng.');
            return;
        }
        btn.closest('tr').remove();
        calculateTotal();
    }

    function calculateSubtotal(element) {
        const tr = element.closest('tr');
        const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        const subtotal = qty * price;

        const subtotalText = tr.querySelector('.subtotal-text');
        if (subtotalText) {
            subtotalText.innerText = new Intl.NumberFormat('vi-VN').format(subtotal) + '\u00A0đ';
        }

        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
            const price = parseFloat(tr.querySelector('.item-price').value) || 0;
            total += qty * price;
        });

        document.getElementById('displayTotal').innerText = new Intl.NumberFormat('vi-VN').format(total) + '\u00A0đ';
    }

    document.getElementById('btnAddRow').addEventListener('click', addRow);

    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endpush
@endsection
