@extends('layouts.app')

@section('title', 'Giá Trị Tồn Kho')
@section('page_title', 'Giá Trị Tồn Kho')

@section('content')
<div class="row g-2 mb-3">
    <div class="col-sm-4">
        <div class="card p-3 mb-0">
            <span class="text-muted small">Tổng số lượng tồn</span>
            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($total_quantity) }} máy</div>
            <small class="text-muted">{{ $products->count() }} mặt hàng</small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-3 mb-0">
            <span class="text-muted small">Tổng giá trị tồn (Giá vốn BQ)</span>
            <div class="fs-4 fw-bold text-primary money-val mt-1 text-nowrap">{{ number_format($total_cost_value) }}&nbsp;đ</div>
            <small class="text-muted">Cập nhật sau mỗi lần nhập (QT6)</small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-3 mb-0">
            <span class="text-muted small">Ước tính doanh thu (Giá bán)</span>
            <div class="fs-4 fw-bold money-val text-dark mt-1 text-nowrap">{{ number_format($total_selling_value) }}&nbsp;đ</div>
            <small class="text-muted text-nowrap">Chênh lệch: <span class="money-val">{{ number_format($total_selling_value - $total_cost_value) }}&nbsp;đ</span></small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('reports.inventory-value') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo mã máy, tên máy tính, hãng..." value="{{ $keyword ?? '' }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Lọc</button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">STT</th>
                    <th>Mã</th>
                    <th>Tên máy</th>
                    <th>Hãng</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Tổng giá vốn</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-end">Tổng doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $p)
                @php
                    $costVal = $p->quantity * (float) $p->cost_price;
                    $sellVal = $p->quantity * (float) $p->selling_price;
                @endphp
                <tr>
                    <td class="text-center small text-muted">{{ $index + 1 }}</td>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $p->code }}</span></td>
                    <td>
                        <div class="fw-medium text-dark">{{ $p->name }}</div>
                        <small class="text-muted">{{ $p->cpu }} | {{ $p->ram }}</small>
                    </td>
                    <td>{{ $p->brand ?? '---' }}</td>
                    <td class="text-center font-monospace fw-bold">{{ $p->quantity }}</td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($p->cost_price) }}&nbsp;đ</td>
                    <td class="text-end money-val bold text-primary">{{ number_format($costVal) }}&nbsp;đ</td>
                    <td class="text-end money-val text-dark" style="font-weight: 500;">{{ number_format($p->selling_price) }}&nbsp;đ</td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($sellVal) }}&nbsp;đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted small">Không tìm thấy sản phẩm nào.</td>
                </tr>
                @endforelse
            </tbody>
            @if($products->isNotEmpty())
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="4" class="text-center">Tổng cộng:</td>
                    <td class="text-center font-monospace">{{ number_format($total_quantity) }}</td>
                    <td></td>
                    <td class="text-end money-val bold text-primary">{{ number_format($total_cost_value) }}&nbsp;đ</td>
                    <td></td>
                    <td class="text-end money-val bold text-dark">{{ number_format($total_selling_value) }}&nbsp;đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
