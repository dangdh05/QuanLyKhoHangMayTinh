<?php

namespace App\Http\Controllers;

use App\Models\InboundNote;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InboundService;
use App\Services\ProductService;
use App\Services\SupplierService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboundController extends Controller
{
    protected InboundService $inboundService;

    protected SupplierService $supplierService;

    protected ProductService $productService;

    protected UserService $userService;

    public function __construct(
        InboundService $inboundService,
        SupplierService $supplierService,
        ProductService $productService,
        UserService $userService
    ) {
        $this->inboundService = $inboundService;
        $this->supplierService = $supplierService;
        $this->productService = $productService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $inboundNotes = $this->inboundService->layDanhSach($request->all());
        $suppliers = Supplier::where('status', true)->get();

        return view('inbounds.index', compact('inboundNotes', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->orderBy('name', 'asc')->get();
        $products = Product::where('status', true)->orderBy('name', 'asc')->get();

        return view('inbounds.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'created_date' => 'nullable|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.import_price' => 'required|numeric|min:1',
        ], [
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập.',
            'items.*.quantity.min' => 'Số lượng nhập phải lớn hơn 0.',
            'items.*.import_price.min' => 'Đơn giá nhập phải lớn hơn 0.',
        ]);

        try {
            $note = $this->inboundService->taoPhieuNhap(
                supplierId: (int) $request->supplier_id,
                userId: Auth::id(),
                items: $request->items,
                note: $request->note,
                createdDate: $request->created_date
            );

            return redirect()->route('inbounds.show', $note->id)
                ->with('success', "Lập phiếu nhập kho [{$note->code}] thành công!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $note = InboundNote::with(['supplier', 'user', 'details.product'])->findOrFail($id);

        return view('inbounds.show', compact('note'));
    }

    public function cancel($id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Chỉ Quản lý hoặc Admin mới có quyền hủy phiếu nhập kho.');
        }

        try {
            $note = $this->inboundService->huyPhieuNhap((int) $id, Auth::id());

            return redirect()->route('inbounds.show', $note->id)
                ->with('success', "Đã hủy phiếu nhập [{$note->code}] và hoàn tác số lượng tồn kho thành công.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
