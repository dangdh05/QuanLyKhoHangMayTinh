<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockCardController;
use App\Http\Controllers\StockCheckController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [UserController::class, 'showLogin'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');
Route::get('/register', [UserController::class, 'showRegister'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register.post');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý mật khẩu
    Route::get('/password/change', [UserController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [UserController::class, 'changePassword'])->name('password.update');

    // Quản lý Sản phẩm / Máy tính
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/stock', [ProductController::class, 'stock'])->name('products.stock');

    // Quản lý Nhà cung cấp
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Quản lý Khách hàng
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Quản lý Phiếu Nhập kho
    Route::get('/inbounds', [InboundController::class, 'index'])->name('inbounds.index');
    Route::get('/inbounds/create', [InboundController::class, 'create'])->name('inbounds.create');
    Route::post('/inbounds', [InboundController::class, 'store'])->name('inbounds.store');
    Route::get('/inbounds/{id}', [InboundController::class, 'show'])->name('inbounds.show');
    Route::post('/inbounds/{id}/cancel', [InboundController::class, 'cancel'])->name('inbounds.cancel');

    // Quản lý Phiếu Xuất kho
    Route::get('/outbounds', [OutboundController::class, 'index'])->name('outbounds.index');
    Route::get('/outbounds/create', [OutboundController::class, 'create'])->name('outbounds.create');
    Route::post('/outbounds', [OutboundController::class, 'store'])->name('outbounds.store');
    Route::get('/outbounds/{id}', [OutboundController::class, 'show'])->name('outbounds.show');
    Route::post('/outbounds/{id}/cancel', [OutboundController::class, 'cancel'])->name('outbounds.cancel');

    // Thẻ kho (Stock Card)
    Route::get('/stock-cards', [StockCardController::class, 'index'])->name('stock-cards.index');

    // Kiểm kê & Điều chỉnh tồn kho
    Route::get('/stock-check', [StockCheckController::class, 'index'])->name('stock-check.index');
    Route::post('/stock-check/adjust', [StockCheckController::class, 'adjust'])->name('stock-check.adjust');

    // Thống kê & Báo cáo
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/in-out-stock', [ReportController::class, 'inOutStock'])->name('in-out-stock');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/inventory-value', [ReportController::class, 'inventoryValue'])->name('inventory-value');
        Route::get('/slow-moving', [ReportController::class, 'slowMoving'])->name('slow-moving');
    });

    // Quản lý tài khoản người dùng (Admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});
