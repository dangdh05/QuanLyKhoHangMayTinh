<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Services\StockCardService;
use Illuminate\Http\Request;

class StockCardController extends Controller
{
    protected StockCardService $stockCardService;

    protected ProductService $productService;

    public function __construct(StockCardService $stockCardService, ProductService $productService)
    {
        $this->stockCardService = $stockCardService;
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $products = Product::where('status', true)->orderBy('name', 'asc')->get();
        $selectedProductId = $request->get('product_id');

        $stockCards = collect();
        $selectedProduct = null;

        if ($selectedProductId) {
            $selectedProduct = Product::find($selectedProductId);
            if ($selectedProduct) {
                $stockCards = $this->stockCardService->layTheKho((int) $selectedProductId);
            }
        } elseif ($products->isNotEmpty()) {
            $selectedProduct = $products->first();
            $selectedProductId = $selectedProduct->id;
            $stockCards = $this->stockCardService->layTheKho($selectedProduct->id);
        }

        return view('stock-cards.index', compact('products', 'selectedProduct', 'selectedProductId', 'stockCards'));
    }
}
