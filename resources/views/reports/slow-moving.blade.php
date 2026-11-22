@extends('layouts.app')

@section('title', 'Hàng Tồn Lâu')
@section('page_title', 'Hàng Tồn Kho Lâu Ngày')

@section('content')
<div class="card mb-3 p-3 bg-light border-0">
    <div class="small text-muted">
        <strong>Giám sát độ tuổi hàng hóa:</strong> Thiết bị công nghệ giảm giá trị nhanh theo thời gian ra mắt. Theo dõi số tháng từ ngày mở bán và số ngày không có phiếu xuất để kịp thời xả hàng.
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('reports.slow-moving') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="mode" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ ($mode ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả hàng tồn đọng</option>
                    <option value="aging_release" {{ ($mode ?? '') === 'aging_release' ? 'selected' : '' }}>Theo thời gian mở bán (&gt; X tháng)</option>
                    <option value="no_outbound" {{ ($mode ?? '') === 'no_outbound' ? 'selected' : '' }}>Theo số ngày không xuất bán</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="aging_months" class="form-select" onchange="this.form.submit()">
                    <option value="6" {{ ($agingMonths ?? 12) == 6 ? 'selected' : '' }}>Mở bán &gt; 6 tháng</option>
                    <option value="12" {{ ($agingMonths ?? 12) == 12 ? 'selected' : '' }}>Mở bán &gt; 12 tháng (1 năm)</option>
                    <option value="18" {{ ($agingMonths ?? 12) == 18 ? 'selected' : '' }}>Mở bán &gt; 18 tháng</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="days" class="form-select" onchange="this.form.submit()">
                    <option value="15" {{ ($days ?? 30) == 15 ? 'selected' : '' }}>Không xuất 15 ngày</option>
                    <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>Không xuất 30 ngày</option>
                    <option value="60" {{ ($days ?? 30) == 60 ? 'selected' : '' }}>Không xuất 60 ngày</option>
                    <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>Không xuất 90 ngày</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>In
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $totalSlowQty = $products->sum('quantity');
    $totalSlowCost = $products->sum(function($p) {
        return $p->quantity * (float) $p->cost_price;
    });
@endphp
<div class="row g-2 mb-3">
    <div class="col-sm-4">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Số loại máy bị đọng</span>
            <div class="fs-5 fw-bold text-danger mt-1">{{ $products->count() }} loại</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Số lượng máy tồn lâu</span>
            <div class="fs-5 fw-bold text-dark mt-1">{{ number_format($totalSlowQty) }} máy</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-2 mb-0">
            <span class="text-muted small">Tổng vốn bị đọng</span>
            <div class="fs-5 fw-bold text-primary money-val mt-1 text-nowrap">{{ number_format($totalSlowCost) }}&nbsp;đ</div>
        </div>
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
                    <th class="text-center">Mở bán</th>
                    <th class="text-center">Thời gian</th>
                    <th class="text-center">Tồn kho</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Vốn đọng</th>
                    <th class="text-center">Mức độ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $p)
                @php
                    $tiedCapital = $p->quantity * (float) $p->cost_price;
                    $months = $p->months_since_release;
                    $isHighRisk = ($months !== null && $months >= 12);
                @endphp
                <tr>
                    <td class="text-center small text-muted">{{ $index + 1 }}</td>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $p->code }}</span></td>
                    <td>
                        <div class="fw-medium text-dark">{{ $p->name }}</div>
                        <small class="text-muted">{{ $p->cpu }} | {{ $p->ram }}</small>
                    </td>
                    <td>{{ $p->brand ?? '---' }}</td>
                    <td class="text-center font-monospace small">
                        {{ $p->release_date?->format('d/m/Y') ?? '---' }}
                    </td>
                    <td class="text-center">
                        @if($months !== null)
                            <span class="small {{ $months >= 12 ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $months }} tháng
                            </span>
                        @else
                            <span class="text-muted small">---</span>
                        @endif
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $isHighRisk ? 'text-danger' : 'text-dark' }}">
                        {{ $p->quantity }}
                    </td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($p->cost_price) }}&nbsp;đ</td>
                    <td class="text-end money-val bold text-danger">{{ number_format($tiedCapital) }}&nbsp;đ</td>
                    <td class="text-center">
                        @if($isHighRisk)
                            <span class="badge bg-danger bg-opacity-10 text-danger">Nguy cơ cao</span>
                        @elseif($months >= 6)
                            <span class="badge bg-warning bg-opacity-10 text-dark">Lưu ý</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Bình thường</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted small">
                        Không ghi nhận mặt hàng nào bị tồn đọng lâu ngày theo tiêu chí lọc.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($products->isNotEmpty())
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="6" class="text-center">Tổng cộng:</td>
                    <td class="text-center font-monospace">{{ number_format($totalSlowQty) }} máy</td>
                    <td></td>
                    <td class="text-end money-val bold text-danger">{{ number_format($totalSlowCost) }}&nbsp;đ</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
