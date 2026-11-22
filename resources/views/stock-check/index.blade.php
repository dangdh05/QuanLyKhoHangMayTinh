@extends('layouts.app')

@section('title', 'Kiểm Kê Kho')
@section('page_title', 'Kiểm Kê Kho')

@section('content')
<div class="card mb-3 p-3 bg-light border-0">
    <div class="small text-muted">
        <strong>Hướng dẫn:</strong> Nhập số lượng thực tế đếm tại kho. Nếu có chênh lệch, vui lòng ghi rõ lý do (thất thoát, hàng lỗi...). Nhấn "Xác nhận điều chỉnh" để hệ thống tự động cập nhật tồn và ghi thẻ kho (QT3, QT4).
    </div>
</div>

<form action="{{ route('stock-check.adjust') }}" method="POST" id="stockCheckForm">
    @csrf
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
            <span class="fw-semibold">Bảng Đối Chiếu Số Liệu</span>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-check2 me-1"></i>Xác nhận điều chỉnh
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">STT</th>
                        <th style="width: 100px;">Mã</th>
                        <th>Tên máy</th>
                        <th style="width: 110px;" class="text-center">Tồn sổ sách</th>
                        <th style="width: 130px;" class="text-center">Tồn thực tế *</th>
                        <th style="width: 100px;" class="text-center">Chênh lệch</th>
                        <th style="min-width: 250px;">Lý do chênh lệch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $p)
                    <tr class="stock-check-row" data-book="{{ $p->quantity }}">
                        <td class="text-center small text-muted">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-monospace text-secondary fw-medium">{{ $p->code }}</span>
                            <input type="hidden" name="adjustments[{{ $index }}][product_id]" value="{{ $p->id }}">
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $p->name }}</div>
                            <small class="text-muted">{{ $p->cpu }} | {{ $p->ram }}</small>
                        </td>
                        <td class="text-center font-monospace fw-bold text-secondary">
                            {{ $p->quantity }}
                        </td>
                        <td>
                            <input type="number" name="adjustments[{{ $index }}][actual_quantity]"
                                   class="form-control text-center fw-bold actual-qty py-1"
                                   min="0" value="{{ old('adjustments.'.$index.'.actual_quantity', $p->quantity) }}"
                                   oninput="calculateDiff(this)">
                        </td>
                        <td class="text-center font-monospace diff-cell">
                            <span class="text-muted">0</span>
                        </td>
                        <td>
                            <input type="text" name="adjustments[{{ $index }}][reason]"
                                   class="form-control reason-input py-1"
                                   placeholder="Lý do..."
                                   value="{{ old('adjustments.'.$index.'.reason') }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="small text-muted" id="summaryDiff">Khớp 100% số liệu.</span>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-check2 me-1"></i>Xác nhận điều chỉnh
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function calculateDiff(input) {
        const tr = input.closest('tr');
        const bookQty = parseInt(tr.getAttribute('data-book')) || 0;
        const actualQty = parseInt(input.value) || 0;
        const diff = actualQty - bookQty;

        const diffCell = tr.querySelector('.diff-cell');
        const reasonInput = tr.querySelector('.reason-input');

        if (diff > 0) {
            diffCell.innerHTML = '<span class="text-success fw-bold">+' + diff + '</span>';
            reasonInput.setAttribute('required', 'required');
        } else if (diff < 0) {
            diffCell.innerHTML = '<span class="text-danger fw-bold">' + diff + '</span>';
            reasonInput.setAttribute('required', 'required');
        } else {
            diffCell.innerHTML = '<span class="text-muted">0</span>';
            reasonInput.removeAttribute('required');
        }

        updateSummary();
    }

    function updateSummary() {
        let diffCount = 0;
        document.querySelectorAll('.stock-check-row').forEach(tr => {
            const bookQty = parseInt(tr.getAttribute('data-book')) || 0;
            const actualQty = parseInt(tr.querySelector('.actual-qty').value) || 0;
            if (actualQty !== bookQty) {
                diffCount++;
            }
        });

        const summarySpan = document.getElementById('summaryDiff');
        if (diffCount > 0) {
            summarySpan.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Có ' + diffCount + ' máy có chênh lệch cần nhập lý do.</span>';
        } else {
            summarySpan.innerHTML = '<span class="text-success"><i class="bi bi-check2 me-1"></i>Số liệu thực tế khớp hoàn toàn.</span>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.actual-qty').forEach(input => {
            calculateDiff(input);
        });
    });
</script>
@endpush
@endsection
