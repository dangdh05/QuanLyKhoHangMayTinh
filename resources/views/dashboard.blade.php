@extends('layouts.app')

@section('title', 'Tổng Quan Kho')
@section('page_title', 'Bảng Điều Khiển')

@section('content')

<!-- Cảnh báo gọn nhẹ (Minimal Alert) -->
@if(isset($agingStockCount) && $agingStockCount > 0)
<div class="alert alert-danger d-flex align-items-center justify-content-between p-2 px-3 border-0 bg-danger bg-opacity-10 text-danger rounded mb-3">
    <div class="d-flex align-items-center gap-2 small">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span>Có <strong>{{ $agingStockCount }}</strong> mặt hàng mở bán &gt; 12 tháng vẫn tồn kho (đọng vốn <strong class="text-nowrap">{{ number_format($totalAgingCapital) }}&nbsp;đ</strong>).</span>
    </div>
    <a href="{{ route('reports.slow-moving', ['mode' => 'aging_release', 'aging_months' => 12]) }}" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-semibold small">
        Xem chi tiết &rarr;
    </a>
</div>
@endif

@if($lowStockCount > 0)
<div class="alert alert-warning d-flex align-items-center justify-content-between p-2 px-3 border-0 bg-warning bg-opacity-10 text-dark rounded mb-3">
    <div class="d-flex align-items-center gap-2 small">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
        <span>Có <strong>{{ $lowStockCount }}</strong> mặt hàng tồn kho chạm mức tối thiểu (QT7).</span>
    </div>
    <a href="{{ route('reports.low-stock') }}" class="btn btn-sm btn-link text-dark p-0 text-decoration-none fw-semibold small">
        Xem hàng sắp hết &rarr;
    </a>
</div>
@endif

<!-- 4 Thẻ KPI tối giản (Minimal Stat Cards) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 p-3">
            <span class="text-muted small fw-medium">Tổng tồn máy tính</span>
            <div class="d-flex align-items-baseline gap-1 mt-1 mb-1">
                <span class="fs-3 fw-bold text-dark">{{ number_format($totalQuantity) }}</span>
                <span class="text-muted small">máy</span>
            </div>
            <span class="text-muted small" style="font-size: 0.78rem;">{{ $totalProducts }} loại sản phẩm</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 p-3">
            <span class="text-muted small fw-medium">Tổng giá trị tồn kho</span>
            <div class="d-flex align-items-baseline gap-1 mt-1 mb-1 text-nowrap">
                <span class="fs-3 fw-bold text-nowrap" style="color: #0284c7;">{{ number_format($totalInventoryValue / 1000000, 1) }}</span>
                <span class="text-muted small text-nowrap">triệu&nbsp;đ</span>
            </div>
            <span class="text-muted small text-nowrap money-val" style="font-size: 0.78rem;">{{ number_format($totalInventoryValue) }}&nbsp;VNĐ</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 p-3">
            <span class="text-muted small fw-medium">Nhập kho tháng này</span>
            <div class="d-flex align-items-baseline gap-1 mt-1 mb-1 text-nowrap">
                <span class="fs-3 fw-bold text-dark text-nowrap">{{ $monthlyInboundsCount }}</span>
                <span class="text-muted small text-nowrap">phiếu</span>
            </div>
            <span class="text-muted small text-nowrap money-val" style="font-size: 0.78rem;">{{ number_format($monthlyInboundsAmount) }}&nbsp;đ</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 p-3">
            <span class="text-muted small fw-medium">Xuất kho tháng này</span>
            <div class="d-flex align-items-baseline gap-1 mt-1 mb-1 text-nowrap">
                <span class="fs-3 fw-bold text-dark text-nowrap">{{ $monthlyOutboundsCount }}</span>
                <span class="text-muted small text-nowrap">phiếu</span>
            </div>
            <span class="text-muted small text-nowrap money-val" style="font-size: 0.78rem;">{{ number_format($monthlyOutboundsAmount) }}&nbsp;đ</span>
        </div>
    </div>
</div>

<!-- Thao tác nhanh tối giản (Minimal Action Bar) -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ route('inbounds.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nhập kho
    </a>
    <a href="{{ route('outbounds.create') }}" class="btn btn-primary">
        <i class="bi bi-box-arrow-up me-1"></i> Xuất kho
    </a>
    <a href="{{ route('products.stock') }}" class="btn btn-outline-primary">
        <i class="bi bi-boxes me-1"></i> Bảng tồn kho
    </a>
    @if(Auth::user()->isManager())
    <a href="{{ route('stock-check.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-clipboard2-check me-1"></i> Kiểm kê
    </a>
    @endif
    <a href="{{ route('reports.slow-moving') }}" class="btn btn-outline-secondary">
        <i class="bi bi-hourglass-split me-1"></i> Hàng tồn lâu
    </a>
</div>

<div class="row g-3">
    <!-- Phiếu nhập gần đây -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <span class="fw-semibold">Phiếu nhập gần đây</span>
                <a href="{{ route('inbounds.index') }}" class="small text-decoration-none">Tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Nhà cung cấp</th>
                            <th>Ngày</th>
                            <th class="text-end">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInbounds as $in)
                        <tr>
                            <td>
                                <a href="{{ route('inbounds.show', $in->id) }}" class="fw-medium font-monospace">
                                    {{ $in->code }}
                                </a>
                            </td>
                            <td>{{ $in->supplier->name ?? '---' }}</td>
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($in->created_date)->format('d/m/Y') }}</td>
                            <td class="text-end money-val">{{ number_format($in->total_amount) }}&nbsp;đ</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted small">Chưa có phiếu nhập</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Phiếu xuất gần đây -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <span class="fw-semibold">Phiếu xuất gần đây</span>
                <a href="{{ route('outbounds.index') }}" class="small text-decoration-none">Tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Khách hàng</th>
                            <th>Ngày</th>
                            <th class="text-end">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOutbounds as $out)
                        <tr>
                            <td>
                                <a href="{{ route('outbounds.show', $out->id) }}" class="fw-medium font-monospace">
                                    {{ $out->code }}
                                </a>
                            </td>
                            <td>{{ $out->customer->name ?? '---' }}</td>
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($out->created_date)->format('d/m/Y') }}</td>
                            <td class="text-end money-val">{{ number_format($out->total_amount) }}&nbsp;đ</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted small">Chưa có phiếu xuất</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
