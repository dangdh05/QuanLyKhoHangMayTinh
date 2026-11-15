@extends('layouts.app')

@section('title', 'Chi Tiết Phiếu Xuất ' . $note->code)
@section('page_title', 'Chi Tiết Phiếu Xuất')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('outbounds.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>In phiếu
        </button>
        @if($note->status === 'completed' && Auth::user()->isManager())
        <form action="{{ route('outbounds.cancel', $note->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy phiếu xuất này? Tồn kho sẽ được cộng hoàn trả (QT5).');">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-x-circle me-1"></i>Hủy phiếu (QT5)
            </button>
        </form>
        @endif
    </div>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">PHIẾU XUẤT KHO</h5>
            <span class="font-monospace text-secondary fw-semibold">{{ $note->code }}</span>
        </div>
        <div class="text-end">
            <span class="badge {{ $note->status === 'completed' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} mb-1">
                {{ $note->status === 'completed' ? 'Hoàn thành' : 'Đã hủy' }}
            </span>
            <div class="small text-muted">{{ \Carbon\Carbon::parse($note->created_date)->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="row mb-3 small">
        <div class="col-md-6">
            <div class="text-muted">Khách hàng / Nơi nhận:</div>
            <div class="fw-semibold text-dark fs-6">{{ $note->customer->name ?? '---' }}</div>
            <div>Mã KH: {{ $note->customer->code ?? '---' }} | SĐT: {{ $note->customer->phone ?? '---' }}</div>
            <div>Địa chỉ giao: {{ $note->customer->address ?? '---' }}</div>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <div>Người lập phiếu: <strong>{{ $note->user->full_name ?? '---' }}</strong></div>
            <div class="text-muted">Ghi chú: {{ $note->note ?: '---' }}</div>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">STT</th>
                    <th>Mã</th>
                    <th>Tên máy tính</th>
                    <th>Cấu hình</th>
                    <th class="text-center" style="width: 100px;">Số lượng</th>
                    <th class="text-end" style="width: 150px;">Đơn giá</th>
                    <th class="text-end" style="width: 160px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($note->details as $index => $detail)
                <tr>
                    <td class="text-center small text-muted">{{ $index + 1 }}</td>
                    <td><span class="font-monospace text-secondary">{{ $detail->product->code ?? '' }}</span></td>
                    <td class="fw-medium text-dark">{{ $detail->product->name ?? '---' }}</td>
                    <td class="small text-muted">{{ $detail->product->cpu ?? '' }} | {{ $detail->product->ram ?? '' }}</td>
                    <td class="text-center font-monospace fw-bold">{{ $detail->quantity }}</td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">{{ number_format($detail->export_price) }}&nbsp;đ</td>
                    <td class="text-end money-val bold text-dark">{{ number_format($detail->subtotal) }}&nbsp;đ</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="6" class="text-end">Tổng cộng:</td>
                    <td class="text-end money-val bold text-primary fs-6">{{ number_format($note->total_amount) }}&nbsp;VNĐ</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row pt-4 text-center small text-muted">
        <div class="col-4">
            <div class="fw-medium text-dark">Người Lập Phiếu</div>
            <div class="mt-4">{{ $note->user->full_name ?? '' }}</div>
        </div>
        <div class="col-4">
            <div class="fw-medium text-dark">Thủ Kho Xuất Hàng</div>
            <div class="mt-4">.........................</div>
        </div>
        <div class="col-4">
            <div class="fw-medium text-dark">Người Nhận Hàng</div>
            <div class="mt-4">.........................</div>
        </div>
    </div>
</div>
@endsection
