@extends('layouts.app')

@section('title', 'Danh Mục Máy Tính')
@section('page_title', 'Danh Mục Máy Tính')

@section('content')
<!-- Filter bar tối giản -->
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo mã, tên máy, CPU, RAM..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
                <select name="brand" class="form-select">
                    <option value="">-- Hãng sản xuất --</option>
                    @php
                        $brands = \App\Models\Product::whereNotNull('brand')->distinct()->pluck('brand');
                    @endphp
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đang kinh doanh</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </button>
            </div>
            <div class="col-md-2 text-end">
                @if(Auth::user()->isManager())
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalCreateProduct">
                    <i class="bi bi-plus-lg me-1"></i>Thêm máy
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
                    <th>Mã</th>
                    <th>Tên máy</th>
                    <th>Cấu hình</th>
                    <th>Mở bán</th>
                    <th class="text-end">Giá vốn</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-center">Tồn kho</th>
                    <th class="text-center">Trạng thái</th>
                    @if(Auth::user()->isManager())
                    <th class="text-end">Thao tác</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <span class="font-monospace fw-medium text-secondary">{{ $product->code }}</span>
                    </td>
                    <td>
                        <div class="fw-medium text-dark">{{ $product->name }}</div>
                        <small class="text-muted">{{ $product->brand ?? '---' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $product->cpu ?? '' }} | {{ $product->ram ?? '' }} | {{ $product->storage ?? '' }}
                        </small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $product->release_date?->format('d/m/Y') ?? '---' }}</small>
                    </td>
                    <td class="text-end money-val text-muted" style="font-weight: 400;">
                        {{ number_format($product->cost_price) }}&nbsp;đ
                    </td>
                    <td class="text-end money-val text-primary" style="font-weight: 600;">
                        {{ number_format($product->selling_price) }}&nbsp;đ
                    </td>
                    <td class="text-center font-monospace">
                        <span class="{{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">
                            {{ $product->quantity }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($product->status)
                            <span class="badge bg-success bg-opacity-10 text-success">Đang bán</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Ngừng</span>
                        @endif
                    </td>
                    @if(Auth::user()->isManager())
                    <td class="text-end text-nowrap">
                        <a href="{{ route('stock-cards.index', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-secondary p-1 px-2" title="Thẻ kho">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2" title="Sửa"
                                data-bs-toggle="modal" data-bs-target="#modalEditProduct{{ $product->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa/ngừng bán máy này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>

                @if(Auth::user()->isManager())
                <!-- Modal Sửa Máy Tính -->
                <div class="modal fade" id="modalEditProduct{{ $product->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('products.update', $product->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h6 class="modal-title fw-semibold">Sửa Máy Tính [{{ $product->code }}]</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Mã máy *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $product->code }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Hãng SX</label>
                                        <input type="text" name="brand" class="form-control" value="{{ $product->brand }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-medium">Tên máy *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Ngày mở bán</label>
                                        <input type="date" name="release_date" class="form-control" value="{{ $product->release_date?->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Giá bán (VNĐ)</label>
                                        <input type="number" step="any" name="selling_price" class="form-control" value="{{ $product->selling_price }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Tồn tối thiểu (QT7)</label>
                                        <input type="number" name="min_quantity" class="form-control" value="{{ $product->min_quantity }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Trạng thái</label>
                                        <select name="status" class="form-select">
                                            <option value="1" {{ $product->status ? 'selected' : '' }}>Đang bán</option>
                                            <option value="0" {{ !$product->status ? 'selected' : '' }}>Ngừng bán</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">CPU</label>
                                        <input type="text" name="cpu" class="form-control" value="{{ $product->cpu }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">RAM</label>
                                        <input type="text" name="ram" class="form-control" value="{{ $product->ram }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Ổ cứng</label>
                                        <input type="text" name="storage" class="form-control" value="{{ $product->storage }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-medium">Card GPU</label>
                                        <input type="text" name="gpu" class="form-control" value="{{ $product->gpu }}">
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
                    <td colspan="9" class="text-center py-4 text-muted small">Không có sản phẩm nào.</td>
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

@if(Auth::user()->isManager())
<!-- Modal Thêm Máy Tính Mới -->
<div class="modal fade" id="modalCreateProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h6 class="modal-title fw-semibold">Thêm Máy Tính Mới</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-medium">Mã máy *</label>
                        <input type="text" name="code" class="form-control" placeholder="VD: DELL-G15" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Hãng SX</label>
                        <input type="text" name="brand" class="form-control" placeholder="VD: Dell, Apple">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-medium">Tên máy *</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Dell Gaming G15 5530" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Ngày mở bán</label>
                        <input type="date" name="release_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Giá bán (VNĐ)</label>
                        <input type="number" step="any" name="selling_price" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Giá vốn ban đầu</label>
                        <input type="number" step="any" name="cost_price" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Tồn tối thiểu (QT7)</label>
                        <input type="number" name="min_quantity" class="form-control" value="5">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">CPU</label>
                        <input type="text" name="cpu" class="form-control" placeholder="VD: Core i7 13650HX">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">RAM</label>
                        <input type="text" name="ram" class="form-control" placeholder="VD: 16GB DDR5">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Ổ cứng</label>
                        <input type="text" name="storage" class="form-control" placeholder="VD: 512GB SSD">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">GPU</label>
                        <input type="text" name="gpu" class="form-control" placeholder="VD: RTX 4060">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu máy tính</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
