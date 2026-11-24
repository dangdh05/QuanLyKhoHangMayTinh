@extends('layouts.app')

@section('title', 'Khách Hàng')
@section('page_title', 'Khách Hàng')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo mã, tên khách hàng, SĐT..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i></button>
            </div>
            <div class="col-md-2 text-end">
                @if(Auth::user()->isManager())
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalCreateCustomer">
                    <i class="bi bi-plus-lg me-1"></i>Thêm KH
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
                    <th>Mã KH</th>
                    <th>Tên khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Trạng thái</th>
                    @if(Auth::user()->isManager())
                    <th class="text-end">Thao tác</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td><span class="font-monospace fw-medium text-secondary">{{ $customer->code }}</span></td>
                    <td class="fw-medium text-dark">{{ $customer->name }}</td>
                    <td>{{ $customer->phone ?? '---' }}</td>
                    <td class="text-muted small">{{ $customer->address ?? '---' }}</td>
                    <td class="text-center">
                        @if($customer->status)
                            <span class="badge bg-success bg-opacity-10 text-success">Hoạt động</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Ngừng</span>
                        @endif
                    </td>
                    @if(Auth::user()->isManager())
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEditCustomer{{ $customer->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa/ngừng giao dịch với khách hàng này?');">
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
                <div class="modal fade" id="modalEditCustomer{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h6 class="modal-title fw-semibold">Sửa Khách Hàng [{{ $customer->code }}]</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Mã khách hàng *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $customer->code }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Tên khách hàng *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Địa chỉ</label>
                                        <textarea name="address" class="form-control" rows="2">{{ $customer->address }}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Trạng thái</label>
                                        <select name="status" class="form-select">
                                            <option value="1" {{ $customer->status ? 'selected' : '' }}>Đang hoạt động</option>
                                            <option value="0" {{ !$customer->status ? 'selected' : '' }}>Ngừng hoạt động</option>
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
                    <td colspan="6" class="text-center py-4 text-muted small">Không tìm thấy khách hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($customers, 'hasPages') && $customers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $customers->total() }} khách hàng</small>
        <div>
            {{ $customers->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>

@if(Auth::user()->isManager())
<div class="modal fade" id="modalCreateCustomer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h6 class="modal-title fw-semibold">Thêm Khách Hàng Mới</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Mã khách hàng *</label>
                        <input type="text" name="code" class="form-control" placeholder="VD: KH004, ANPHAT" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Tên khách hàng *</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Công ty CP Máy Tính An Phát" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0912345678">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Địa chỉ giao nhận hàng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu KH</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
