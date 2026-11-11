<?php

use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
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
    Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('logout');
});
