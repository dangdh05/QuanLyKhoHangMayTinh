@extends('layouts.app')

@section('title', 'Nhà Cung Cấp')
@section('page_title', 'Nhà Cung Cấp')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo mã, tên, SĐT..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đang hợp tác</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ngừng hợp tác</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i></button>
            </div>
            <div class="col-md-2 text-end">
                @if(Auth::user()->isManager())
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">
                    <i class="bi bi-plus-lg me-1"></i>Thêm NCC
                </button>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mã NCC</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Số điện thoại</th>
                    <th>Email</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Trạng thái</th>
                    @if(Auth::user()->isManager())
                    <th class="text-end">Thao tác</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $supplier->code }}</span></td>
                    <td class="fw-medium text-dark">{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?? '---' }}</td>
                    <td class="text-muted small">{{ $supplier->email ?? '---' }}</td>
                    <td class="text-muted small">{{ $supplier->address ?? '---' }}</td>
                    <td class="text-center">
                        @if($supplier->status)
                            <span class="badge bg-success bg-opacity-10 text-success">Đang hợp tác</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Ngừng</span>
                        @endif
                    </td>
                    @if(Auth::user()->isManager())
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEditSupplier{{ $supplier->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa/ngừng hợp tác NCC này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>

                @if(Auth::user()->isManager())
                <div class="modal fade" id="modalEditSupplier{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h6 class="modal-title fw-semibold">Sửa Nhà Cung Cấp [{{ $supplier->code }}]</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Mã nhà cung cấp *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $supplier->code }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Tên nhà cung cấp *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control" value="{{ $supplier->phone }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $supplier->email }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Địa chỉ</label>
                                        <textarea name="address" class="form-control" rows="2">{{ $supplier->address }}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Trạng thái</label>
                                        <select name="status" class="form-select">
                                            <option value="1" {{ $supplier->status ? 'selected' : '' }}>Đang hợp tác</option>
                                            <option value="0" {{ !$supplier->status ? 'selected' : '' }}>Ngừng hợp tác</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer p-2">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-sm btn-primary">Lưu thay đổi</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted small">Không tìm thấy nhà cung cấp nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($suppliers, 'hasPages') && $suppliers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $suppliers->total() }} nhà cung cấp</small>
        <div>
            {{ $suppliers->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>

@if(Auth::user()->isManager())
<div class="modal fade" id="modalCreateSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h6 class="modal-title fw-semibold">Thêm Nhà Cung Cấp Mới</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Mã nhà cung cấp *</label>
                        <input type="text" name="code" class="form-control" placeholder="VD: NCC004, DGW" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Tên nhà cung cấp *</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Công ty TNHH Synnex FPT" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 02473000000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@fpt.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Địa chỉ trụ sở / kho hàng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu NCC</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
