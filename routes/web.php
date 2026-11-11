<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\StaffAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contactos', [HomeController::class, 'storeContact'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/media/products/{filename}', [ProductMediaController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('media.products.show');

Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::get('/produtos/{product}', [MarketplaceController::class, 'show'])
        ->whereNumber('product')
        ->name('products.show');
});

Route::group(function (): void {
    Route::get('/entrar', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/entrar', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('customer.login.store');
    Route::get('/registar', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/registar', [CustomerAuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('customer.register.store');
});

Route::middleware(['auth:customer', 'customer.active'])->prefix('conta')->name('customer.')->group(function (): void {
    Route::get('/', [CustomerPortalController::class, 'index'])->name('dashboard');
    Route::get('/pedidos/{order}', [CustomerOrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('/proformas/{proforma}', [CustomerOrderController::class, 'showProforma'])->whereNumber('proforma')->name('proformas.show');
    Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('logout');
});

Route::get('/admin/entrar', [StaffAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/entrar', [StaffAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('admin.login.store');

Route::middleware(['auth:web', 'staff.active'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/sair', [StaffAuthController::class, 'logout'])->name('logout');

    Route::middleware('staff.role:admin,seller,stock_manager')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('staff.role:admin,seller')->prefix('pedidos')->name('orders.')->group(function (): void {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->whereNumber('order')->name('show');
    });
});
