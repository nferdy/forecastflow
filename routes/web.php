<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

/* ---------------- Etalase publik (B2C / B2B) ---------------- */
Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/produk/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/tambah/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/keranjang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{product}', [CartController::class, 'remove'])->name('cart.remove');

/* ---------------- Autentikasi ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* ---------------- Area pelanggan ---------------- */
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/bukti', [CustomerOrderController::class, 'uploadProof'])->name('orders.proof');
    Route::post('/pesanan/{order}/batal', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
});

/* ---------------- Dashboard Admin ---------------- */
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Prediksi stok
    Route::get('/prediksi', [Admin\ForecastController::class, 'index'])->name('forecast.index');
    Route::post('/prediksi/pengaturan', [Admin\ForecastController::class, 'settings'])->name('forecast.settings');
    Route::get('/prediksi/{product}', [Admin\ForecastController::class, 'show'])->name('forecast.show');

    // Pesanan
    Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pesanan/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');

    // Inventaris
    Route::resource('products', Admin\ProductController::class)->except('show');
    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('suppliers', Admin\SupplierController::class)->except('show');
    Route::get('/stok', [Admin\StockController::class, 'index'])->name('stock.index');
    Route::post('/stok', [Admin\StockController::class, 'store'])->name('stock.store');

    // Pengadaan (Auto-PO)
    Route::get('/po', [Admin\PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/buat', [Admin\PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [Admin\PurchaseOrderController::class, 'store'])->name('po.store');
    Route::post('/po/auto', [Admin\PurchaseOrderController::class, 'auto'])->name('po.auto');
    Route::get('/po/{purchaseOrder}', [Admin\PurchaseOrderController::class, 'show'])->name('po.show');
    Route::get('/po/{purchaseOrder}/pdf', [Admin\PurchaseOrderController::class, 'pdf'])->name('po.pdf');
    Route::post('/po/{purchaseOrder}/kirim', [Admin\PurchaseOrderController::class, 'send'])->name('po.send');
    Route::post('/po/{purchaseOrder}/tandai-terkirim', [Admin\PurchaseOrderController::class, 'markSent'])->name('po.marksent');
    Route::post('/po/{purchaseOrder}/terima', [Admin\PurchaseOrderController::class, 'receive'])->name('po.receive');
    Route::delete('/po/{purchaseOrder}', [Admin\PurchaseOrderController::class, 'destroy'])->name('po.destroy');
});
