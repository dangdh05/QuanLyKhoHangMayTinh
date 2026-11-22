<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OutboundNote;
use App\Models\Product;
use App\Services\CustomerService;
use App\Services\OutboundService;
use App\Services\ProductService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutboundController extends Controller
{
    protected OutboundService $outboundService;

    protected CustomerService $customerService;

    protected ProductService $productService;

    protected UserService $userService;

    public function __construct(
        OutboundService $outboundService,
        CustomerService $customerService,
        ProductService $productService,
        UserService $userService
    ) {
        $this->outboundService = $outboundService;
        $this->customerService = $customerService;
        $this->productService = $productService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $outboundNotes = $this->outboundService->layDanhSach($request->all());
        $customers = Customer::where('status', true)->get();

        return view('outbounds.index', compact('outboundNotes', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)->orderBy('name', 'asc')->get();
        $products = Product::where('status', true)
            ->where('quantity', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('outbounds.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'created_date' => 'nullable|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.export_price' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào phiếu xuất.',
            'items.*.quantity.min' => 'Số lượng xuất phải lớn hơn 0.',
        ]);

        try {
            $result = $this->outboundService->taoPhieuXuat(
                customerId: (int) $request->customer_id,
                userId: Auth::id(),
                items: $request->items,
                note: $request->note,
                createdDate: $request->created_date
            );

            $note = $result['note'];
            $msg = "Lập phiếu xuất kho [{$note->code}] thành công!";

            if (! empty($result['low_stock_warnings'])) {
                $warnCount = count($result['low_stock_warnings']);
                session()->flash('warning', "Cảnh báo: Có {$warnCount} mặt hàng vừa chạm mức tồn tối thiểu sau lần xuất này!");
            }

            return redirect()->route('outbounds.show', $note->id)->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $note = OutboundNote::with(['customer', 'user', 'details.product'])->findOrFail($id);

        return view('outbounds.show', compact('note'));
    }

    public function cancel($id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Chỉ Quản lý hoặc Admin mới có quyền hủy phiếu xuất kho.');
        }

        try {
            $note = $this->outboundService->huyPhieuXuat((int) $id, Auth::id());

            return redirect()->route('outbounds.show', $note->id)
                ->with('success', "Đã hủy phiếu xuất [{$note->code}] và hoàn trả tồn kho thành công.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
