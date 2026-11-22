@extends('layouts.app')

@section('title', 'Phiếu Nhập Kho')
@section('page_title', 'Phiếu Nhập Kho')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('inbounds.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control" placeholder="Mã phiếu hoặc tên NCC..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <select name="supplier_id" class="form-select">
                    <option value="">-- Tất cả nhà cung cấp --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('inbounds.create') }}" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg me-1"></i>Lập phiếu nhập
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Nhà cung cấp</th>
                    <th>Ngày lập</th>
                    <th>Người lập</th>
                    <th class="text-center">Số dòng</th>
                    <th class="text-end">Tổng tiền (VNĐ)</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inboundNotes as $note)
                <tr>
                    <td>
                        <a href="{{ route('inbounds.show', $note->id) }}" class="fw-medium font-monospace">
                            {{ $note->code }}
                        </a>
                    </td>
                    <td>
                        <div>{{ $note->supplier->name ?? '---' }}</div>
                    </td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($note->created_date)->format('d/m/Y H:i') }}</td>
                    <td class="small">{{ $note->user->full_name ?? '---' }}</td>
                    <td class="text-center">{{ $note->details->count() }}</td>
                    <td class="text-end money-val bold text-dark">{{ number_format($note->total_amount) }}&nbsp;đ</td>
                    <td class="text-center">
                        @if($note->status === 'completed')
                            <span class="badge bg-success bg-opacity-10 text-success">Hoàn thành</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">Đã hủy</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('inbounds.show', $note->id) }}" class="btn btn-sm btn-outline-primary p-1 px-2">
                            Chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted small">Không tìm thấy phiếu nhập kho nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($inboundNotes, 'hasPages') && $inboundNotes->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $inboundNotes->total() }} phiếu</small>
        <div>
            {{ $inboundNotes->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
