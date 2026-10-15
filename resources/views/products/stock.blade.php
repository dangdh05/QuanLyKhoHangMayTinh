@extends('layouts.app')

@section('title', 'Tồn Kho Hiện Tại')
@section('page_title', 'Tồn Kho Hiện Tại')

@section('content')
<!-- Filter Bar -->
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('products.stock') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo mã máy, tên máy, hãng..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-4">
                <select name="filter_type" class="form-select">
                    <option value="">-- Tất cả mặt hàng tồn kho --</option>
                    <option value="aging" {{ request('filter_type') === 'aging' ? 'selected' : '' }}>Hàng tồn &gt; 12 tháng (QT8)</option>
                    <option value="warning_aging" {{ request('filter_type') === 'warning_aging' ? 'selected' : '' }}>Hàng mở bán &gt; 6 tháng</option>
                    <option value="low_stock" {{ request('filter_type') === 'low_stock' ? 'selected' : '' }}>Hàng sắp hết &le; định mức (QT7)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>
                @if(request()->hasAny(['keyword', 'filter_type']))
                <a href="{{ route('products.stock') }}" class="btn btn-light border" title="Xóa lọc">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
        <span class="fw-semibold">Danh Sách Tồn Kho</span>
        <div class="d-flex gap-2">
            <a href="{{ route('stock-cards.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i>Thẻ kho
            </a>
            @if(Auth::user()->isManager())
            <a href="{{ route('stock-check.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-clipboard2-check me-1"></i>Kiểm kê
            </a>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên máy tính</th>
                    <th>Hãng</th>
                    <th class="text-center">Mở bán</th>
                    <th class="text-center">Số lượng tồn</th>
                    <th class="text-center">Tối thiểu</th>
                    <th class="text-center">Tình trạng</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Giá trị tồn</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                @php
                    $isAging = $p->isAgingOverstock(12);
                    $months = $p->months_since_release;
                @endphp
                <tr>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $p->code }}</span></td>
                    <td>
                        <div class="fw-medium text-dark">{{ $p->name }}</div>
                        <small class="text-muted">{{ $p->cpu }} | {{ $p->ram }}</small>
                    </td>
                    <td>{{ $p->brand ?? '---' }}</td>
                    <td class="text-center font-monospace small text-muted">
                        {{ $p->release_date?->format('d/m/Y') ?? '---' }}
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $p->isLowStock() ? 'text-danger' : 'text-dark' }}">
                        {{ $p->quantity }}
                    </td>
                    <td class="text-center font-monospace text-muted">{{ $p->min_quantity }}</td>
                    <td class="text-center">
                        @if($isAging)
                            <span class="badge bg-danger bg-opacity-10 text-danger">Tồn lâu (&gt;1 năm)</span>
                        @elseif($p->isLowStock())
                            <span class="badge bg-warning bg-opacity-10 text-dark">Sắp hết</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success">Đủ</span>
                        @endif
                    </td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($p->cost_price) }}&nbsp;đ</td>
                    <td class="text-end money-val bold text-dark">{{ number_format($p->inventory_value) }}&nbsp;đ</td>
                    <td class="text-end">
                        <a href="{{ route('stock-cards.index', ['product_id' => $p->id]) }}" class="btn btn-sm btn-outline-secondary p-1 px-2" title="Thẻ kho">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted small">Không tìm thấy sản phẩm nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($products, 'hasPages') && $products->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $products->total() }} máy</small>
        <div>
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
