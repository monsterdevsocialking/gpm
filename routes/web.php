<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contactos', [HomeController::class, 'storeContact'])->name('contact.store');

Route::get('/media/products/{filename}', [ProductMediaController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('media.products.show');

Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::get('/produtos/{product}', [MarketplaceController::class, 'show'])
        ->whereNumber('product')
        ->name('products.show');
});
