@extends('layouts.app')

@section('title', 'Báo Cáo Hàng Sắp Hết')
@section('page_title', 'Hàng Tồn Dưới Định Mức (QT7)')

@section('content')
<div class="card mb-3 p-3 bg-light border-0 d-flex flex-row justify-content-between align-items-center">
    <div class="small text-muted">
        Mặt hàng có số lượng tồn &le; định mức tối thiểu. Cần lên kế hoạch nhập hàng bổ sung.
    </div>
    <a href="{{ route('inbounds.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Lập phiếu nhập
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">STT</th>
                    <th>Mã</th>
                    <th>Tên máy</th>
                    <th>Cấu hình</th>
                    <th class="text-center">Tồn hiện tại</th>
                    <th class="text-center">Tối thiểu</th>
                    <th class="text-center">Đề xuất nhập</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockProducts as $index => $p)
                @php
                    $needQty = max(1, ($p->min_quantity * 2) - $p->quantity);
                @endphp
                <tr>
                    <td class="text-center small text-muted">{{ $index + 1 }}</td>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $p->code }}</span></td>
                    <td class="fw-medium text-dark">{{ $p->name }}</td>
                    <td class="small text-muted">{{ $p->cpu }} | {{ $p->ram }}</td>
                    <td class="text-center font-monospace fw-bold text-danger">{{ $p->quantity }}</td>
                    <td class="text-center font-monospace text-muted">{{ $p->min_quantity }}</td>
                    <td class="text-center font-monospace text-primary fw-medium">&ge; {{ $needQty }}</td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($p->cost_price) }}&nbsp;đ</td>
                    <td class="text-end">
                        <a href="{{ route('inbounds.create') }}" class="btn btn-sm btn-outline-primary p-1 px-2">
                            Nhập hàng
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted small">
                        Hiện không có mặt hàng nào dưới định mức tồn tối thiểu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
