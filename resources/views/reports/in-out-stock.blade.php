@extends('layouts.app')

@section('title', 'Báo Cáo Nhập Xuất Tồn')
@section('page_title', 'Báo Cáo Nhập – Xuất – Tồn')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('reports.in-out-stock') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="keyword" class="form-control" placeholder="Mã hoặc tên máy..." value="{{ $keyword ?? '' }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Xem báo cáo
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
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
                    <th>Tên máy tính</th>
                    <th class="text-center">Đầu kỳ</th>
                    <th class="text-center text-primary">Nhập (+)</th>
                    <th class="text-center">Xuất (-)</th>
                    <th class="text-center">Điều chỉnh</th>
                    <th class="text-center">Cuối kỳ</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Giá trị cuối</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumTonDau = 0;
                    $sumNhap = 0;
                    $sumXuat = 0;
                    $sumDieuChinh = 0;
                    $sumTonCuoi = 0;
                    $sumGiaTri = 0;
                @endphp
                @forelse($reportData as $index => $row)
                @php
                    $sumTonDau += $row['ton_dau'];
                    $sumNhap += $row['nhap'];
                    $sumXuat += $row['xuat'];
                    $sumDieuChinh += $row['dieu_chinh'];
                    $sumTonCuoi += $row['ton_cuoi'];
                    $sumGiaTri += $row['gia_tri_ton_cuoi'];
                    $isLow = $row['ton_cuoi'] <= $row['product']->min_quantity;
                @endphp
                <tr>
                    <td class="text-center small text-muted">{{ $index + 1 }}</td>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $row['product']->code }}</span></td>
                    <td class="fw-medium text-dark">{{ $row['product']->name }}</td>
                    <td class="text-center font-monospace">{{ $row['ton_dau'] }}</td>
                    <td class="text-center font-monospace text-primary fw-medium">{{ $row['nhap'] > 0 ? '+'.$row['nhap'] : 0 }}</td>
                    <td class="text-center font-monospace">{{ $row['xuat'] > 0 ? '-'.$row['xuat'] : 0 }}</td>
                    <td class="text-center font-monospace small text-muted">
                        {{ $row['dieu_chinh'] >= 0 ? ($row['dieu_chinh'] > 0 ? '+'.$row['dieu_chinh'] : 0) : $row['dieu_chinh'] }}
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $isLow ? 'text-danger' : 'text-dark' }}">
                        {{ $row['ton_cuoi'] }}
                    </td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($row['gia_von']) }}&nbsp;đ</td>
                    <td class="text-end money-val bold text-dark">{{ number_format($row['gia_tri_ton_cuoi']) }}&nbsp;đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted small">Không có dữ liệu mặt hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($reportData) > 0)
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="3" class="text-center">Tổng cộng:</td>
                    <td class="text-center font-monospace">{{ $sumTonDau }}</td>
                    <td class="text-center font-monospace text-primary">+{{ $sumNhap }}</td>
                    <td class="text-center font-monospace">-{{ $sumXuat }}</td>
                    <td class="text-center font-monospace">{{ $sumDieuChinh >= 0 ? '+'.$sumDieuChinh : $sumDieuChinh }}</td>
                    <td class="text-center font-monospace">{{ $sumTonCuoi }}</td>
                    <td></td>
                    <td class="text-end money-val bold text-primary">{{ number_format($sumGiaTri) }}&nbsp;đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
