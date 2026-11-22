@extends('layouts.app')

@section('title', 'Lập Phiếu Xuất')
@section('page_title', 'Lập Phiếu Xuất Kho')

@section('content')
<form action="{{ route('outbounds.store') }}" method="POST" id="outboundForm">
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
                        <label class="form-label small fw-medium">Khách hàng / Nơi nhận * (QT2)</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    [{{ $c->code }}] {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-medium">Ngày xuất</label>
                        <input type="datetime-local" name="created_date" class="form-control" value="{{ old('created_date', now()->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-medium">Người lập</label>
                        <input type="text" class="form-control bg-light" value="{{ Auth::user()->full_name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Hợp đồng, số hóa đơn xuất...">{{ old('note') }}</textarea>
                    </div>

                    <div class="p-3 bg-light rounded border-0">
                        <div class="text-muted small">Tổng tiền xuất:</div>
                        <h4 class="fw-bold text-primary money-val mb-0 text-nowrap" id="displayTotal">0&nbsp;đ</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi tiết mặt hàng -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="fw-semibold">Danh Sách Máy Xuất</span>
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
                                    <th style="width: 20%;">Số lượng xuất *</th>
                                    <th style="width: 25%;">Đơn giá xuất (VNĐ) *</th>
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
                    <a href="{{ route('outbounds.index') }}" class="btn btn-sm btn-light border">Hủy</a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2 me-1"></i>Lưu & Trừ tồn kho (QT2, QT3)
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][product_id]" class="form-select product-select" required onchange="onProductSelect(this)">
                <option value="">-- Chọn máy tính xuất --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-qty="{{ $p->quantity }}">
                        [{{ $p->code }}] {{ $p->name }} (Còn tồn: {{ $p->quantity }})
                    </option>
                @endforeach
            </select>
            <div class="small text-muted mt-1 stock-info"></div>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control item-qty" min="1" value="1" required oninput="calculateSubtotal(this)">
            <div class="small text-danger mt-1 error-qty" style="display:none;">Vượt quá tồn!</div>
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][export_price]" class="form-control item-price" min="0" placeholder="Đơn giá xuất..." required oninput="calculateSubtotal(this)">
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
            alert('Phiếu xuất phải có ít nhất một mặt hàng.');
            return;
        }
        btn.closest('tr').remove();
        calculateTotal();
    }

    function onProductSelect(selectElement) {
        const tr = selectElement.closest('tr');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const stock = parseInt(selectedOption.getAttribute('data-qty')) || 0;

        const priceInput = tr.querySelector('.item-price');
        priceInput.value = price;

        const stockInfo = tr.querySelector('.stock-info');
        if (selectedOption.value) {
            stockInfo.innerHTML = '<span class="text-success small">Tồn khả dụng: <strong>' + stock + '</strong></span>';
            tr.querySelector('.item-qty').setAttribute('max', stock);
        } else {
            stockInfo.innerHTML = '';
        }

        calculateSubtotal(selectElement);
    }

    function calculateSubtotal(element) {
        const tr = element.closest('tr');
        const qtyInput = tr.querySelector('.item-qty');
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        const subtotal = qty * price;

        const select = tr.querySelector('.product-select');
        const selectedOption = select.options[select.selectedIndex];
        const stock = selectedOption ? parseInt(selectedOption.getAttribute('data-qty')) || 0 : 0;

        const errorDiv = tr.querySelector('.error-qty');
        if (selectedOption && selectedOption.value && qty > stock) {
            errorDiv.style.display = 'block';
            errorDiv.innerText = 'Vượt quá tồn (còn ' + stock + ')';
            qtyInput.classList.add('is-invalid');
        } else {
            errorDiv.style.display = 'none';
            qtyInput.classList.remove('is-invalid');
        }

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
