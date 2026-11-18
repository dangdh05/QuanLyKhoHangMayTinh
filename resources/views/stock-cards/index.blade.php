@extends('layouts.app')

@section('title', 'Thẻ Kho')
@section('page_title', 'Thẻ Kho')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('stock-cards.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <select name="product_id" class="form-select" onchange="this.form.submit()">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ $selectedProductId == $p->id ? 'selected' : '' }}>
                            [{{ $p->code }}] {{ $p->name }} (Tồn: {{ $p->quantity }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('products.stock') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-boxes me-1"></i>Bảng tồn kho
                </a>
            </div>
        </form>
    </div>
</div>

@if($selectedProduct)
<div class="row g-2 mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Mặt hàng</span>
            <div class="fw-semibold text-truncate">{{ $selectedProduct->name }}</div>
            <small class="text-muted font-monospace">{{ $selectedProduct->code }}</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Tồn kho hiện tại</span>
            <div class="fs-5 fw-bold {{ $selectedProduct->isLowStock() ? 'text-danger' : 'text-dark' }}">
                {{ $selectedProduct->quantity }} máy
            </div>
            <small class="text-muted">Tối thiểu: {{ $selectedProduct->min_quantity }}</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Giá vốn bình quân</span>
            <div class="fs-5 fw-bold text-primary money-val text-nowrap">
                {{ number_format($selectedProduct->cost_price) }}&nbsp;đ
            </div>
            <small class="text-muted">Theo QT6</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Tổng giá trị tồn</span>
            <div class="fs-5 fw-bold money-val text-dark text-nowrap">
                {{ number_format($selectedProduct->inventory_value) }}&nbsp;đ
            </div>
            <small class="text-muted">Số lượng &times; Giá vốn</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Loại giao dịch</th>
                    <th>Chứng từ</th>
                    <th class="text-center">Biến động</th>
                    <th class="text-center">Số dư tồn</th>
                    <th>Diễn giải</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockCards as $card)
                <tr>
                    <td class="small text-muted">{{ $card->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($card->transaction_type === 'inbound')
                            <span class="badge bg-primary bg-opacity-10 text-primary">Nhập kho</span>
                        @elseif($card->transaction_type === 'outbound')
                            <span class="badge bg-warning bg-opacity-10 text-dark">Xuất kho</span>
                        @elseif($card->transaction_type === 'adjust')
                            <span class="badge bg-info bg-opacity-10 text-dark">Điều chỉnh</span>
                        @elseif($card->transaction_type === 'cancel_inbound')
                            <span class="badge bg-danger bg-opacity-10 text-danger">Hủy nhập</span>
                        @elseif($card->transaction_type === 'cancel_outbound')
                            <span class="badge bg-success bg-opacity-10 text-success">Hủy xuất</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $card->transaction_type }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="font-monospace fw-medium">{{ $card->ref_code }}</span>
                    </td>
                    <td class="text-center font-monospace">
                        @if($card->change_quantity > 0)
                            <span class="text-success fw-medium">+{{ $card->change_quantity }}</span>
                        @elseif($card->change_quantity < 0)
                            <span class="text-danger fw-medium">{{ $card->change_quantity }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="text-center font-monospace fw-bold">
                        {{ $card->balance_quantity }}
                    </td>
                    <td class="small text-muted">
                        {{ $card->note ?: '---' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted small">
                        Mặt hàng này chưa phát sinh biến động tồn kho.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($stockCards, 'hasPages') && $stockCards->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $stockCards->total() }} dòng</small>
        <div>
            {{ $stockCards->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endif
@endsection
