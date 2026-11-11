<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminConversationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProposalController;
use App\Http\Controllers\AdminProformaController;
use App\Http\Controllers\AdminQuoteRequestController;
use App\Http\Controllers\AdminSupplierController;
use App\Http\Controllers\AdminStockController;
use App\Http\Controllers\AdminSupplierInquiryController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerConversationController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CustomerProposalController;
use App\Http\Controllers\CustomerQuoteRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\QuoteCartController;
use App\Http\Controllers\StaffAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contactos', [HomeController::class, 'storeContact'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/media/products/{filename}', [ProductMediaController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('media.products.show');

Route::get('/carrinho', [QuoteCartController::class, 'index'])->name('cart.index');
Route::post('/carrinho/produtos/{product}', [QuoteCartController::class, 'store'])
    ->whereNumber('product')
    ->name('cart.items.store');
Route::patch('/carrinho', [QuoteCartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/produtos/{product}', [QuoteCartController::class, 'destroy'])
    ->whereNumber('product')
    ->name('cart.items.destroy');
Route::post('/carrinho/enviar', [QuoteCartController::class, 'submit'])->name('cart.submit');

Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::get('/produtos/{product}', [MarketplaceController::class, 'show'])
        ->whereNumber('product')
        ->name('products.show');
});

Route::group([], function (): void {
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
    Route::get('/pedidos/{order}', [CustomerOrderController::class, 'show'])
        ->whereNumber('order')
        ->name('orders.show');
    Route::get('/pedidos/{order}/conversa', [CustomerConversationController::class, 'openForOrder'])
        ->whereNumber('order')
        ->name('orders.conversation');
    Route::get('/conversas/{conversation}', [CustomerConversationController::class, 'show'])
        ->whereNumber('conversation')
        ->name('conversations.show');
    Route::post('/conversas/{conversation}/mensagens', [CustomerConversationController::class, 'send'])
        ->whereNumber('conversation')
        ->middleware('throttle:10,1')
        ->name('conversations.messages.store');
    Route::get('/proformas/{proforma}', [CustomerOrderController::class, 'showProforma'])
        ->whereNumber('proforma')
        ->name('proformas.show');
    Route::get('/solicitacoes/{quoteRequest}', [CustomerQuoteRequestController::class, 'show'])
        ->whereNumber('quoteRequest')
        ->name('requests.show');
    Route::get('/perfil', [CustomerAccountController::class, 'editProfile'])->name('profile.edit');
    Route::put('/perfil', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/seguranca/palavra-passe', [CustomerAccountController::class, 'editPassword'])->name('password.edit');
    Route::put('/seguranca/palavra-passe', [CustomerAccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/propostas/{proposalVersion}', [CustomerProposalController::class, 'show'])
        ->whereNumber('proposalVersion')
        ->name('proposals.show');
    Route::post('/propostas/{proposalVersion}/decisao', [CustomerProposalController::class, 'decide'])
        ->whereNumber('proposalVersion')
        ->middleware('throttle:5,1')
        ->name('proposals.decide');
    Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('logout');
});

Route::get('/admin/entrar', [StaffAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/entrar', [StaffAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('admin.login.store');

Route::middleware(['auth:web', 'staff.active'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('staff.role:admin')->group(function (): void {
        Route::get('/atividade', [AdminActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/utilizadores', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/utilizadores', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('/utilizadores/{user}/estado', [AdminUserController::class, 'toggleActive'])
            ->whereNumber('user')
            ->name('users.toggle-active');
        Route::get('/empresa', [AdminCompanyController::class, 'edit'])->name('company.edit');
        Route::put('/empresa', [AdminCompanyController::class, 'update'])->name('company.update');
    });

    Route::post('/sair', [StaffAuthController::class, 'logout'])->name('logout');

    Route::middleware('staff.role:admin,seller,stock_manager')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('staff.role:admin,stock_manager')->prefix('stock')->name('stock.')->group(function (): void {
        Route::get('/', [AdminStockController::class, 'index'])->name('index');
        Route::post('/', [AdminStockController::class, 'store'])->name('store');
    });

    Route::middleware('staff.role:admin,seller')->group(function (): void {
        Route::get('/contactos', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/conversas', [AdminConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversas/{conversation}', [AdminConversationController::class, 'show'])
            ->whereNumber('conversation')
            ->name('conversations.show');
        Route::post('/conversas/{conversation}/mensagens', [AdminConversationController::class, 'send'])
            ->whereNumber('conversation')
            ->middleware('throttle:20,1')
            ->name('conversations.messages.store');
        Route::patch('/conversas/{conversation}/estado', [AdminConversationController::class, 'toggleStatus'])
            ->whereNumber('conversation')
            ->name('conversations.toggle-status');

        Route::get('/clientes', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::patch('/clientes/{customer}/estado', [AdminCustomerController::class, 'toggleActive'])
            ->whereNumber('customer')
            ->name('customers.toggle-active');

        Route::get('/catalogo', [AdminCatalogController::class, 'index'])->name('catalog.index');
        Route::post('/catalogo', [AdminCatalogController::class, 'store'])->name('catalog.store');
        Route::put('/catalogo/{product}', [AdminCatalogController::class, 'update'])
            ->whereNumber('product')
            ->name('catalog.update');
        Route::delete('/catalogo/{product}', [AdminCatalogController::class, 'destroy'])
            ->whereNumber('product')
            ->name('catalog.destroy');
        Route::delete('/catalogo/{product}/imagens/{image}', [AdminCatalogController::class, 'destroyImage'])
            ->whereNumber('product')
            ->whereNumber('image')
            ->name('catalog.images.destroy');

        Route::prefix('categorias')->name('categories.')->group(function (): void {
            Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
            Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [AdminCategoryController::class, 'update'])
                ->whereNumber('category')
                ->name('update');
            Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])
                ->whereNumber('category')
                ->name('destroy');
        });

        Route::prefix('fornecedores')->name('suppliers.')->group(function (): void {
            Route::get('/', [AdminSupplierController::class, 'index'])->name('index');
            Route::post('/', [AdminSupplierController::class, 'store'])->name('store');
            Route::put('/{supplier}', [AdminSupplierController::class, 'update'])
                ->whereNumber('supplier')
                ->name('update');
        });

        Route::post('/solicitacoes/{quoteRequest}/propostas', [AdminProposalController::class, 'startDraft'])
            ->whereNumber('quoteRequest')
            ->name('requests.proposals.store');
        Route::get('/propostas/{proposalVersion}', [AdminProposalController::class, 'show'])
            ->whereNumber('proposalVersion')
            ->name('proposals.show');
        Route::put('/propostas/{proposalVersion}', [AdminProposalController::class, 'updateDraft'])
            ->whereNumber('proposalVersion')
            ->name('proposals.update');
        Route::post('/propostas/{proposalVersion}/emitir', [AdminProposalController::class, 'issue'])
            ->whereNumber('proposalVersion')
            ->name('proposals.issue');
        Route::post('/propostas/{proposalVersion}/confirmar-encomenda', [AdminProposalController::class, 'confirmOrder'])
            ->whereNumber('proposalVersion')
            ->name('proposals.confirm-order');

        Route::post('/solicitacoes/{quoteRequest}/consultas-fornecedores', [AdminSupplierInquiryController::class, 'storeForRequest'])
            ->whereNumber('quoteRequest')
            ->name('requests.inquiries.store');
        Route::get('/consultas-fornecedores/{inquiry}', [AdminSupplierInquiryController::class, 'show'])
            ->whereNumber('inquiry')
            ->name('supplier-inquiries.show');
        Route::patch('/consultas-fornecedores/{inquiry}/enviar', [AdminSupplierInquiryController::class, 'markSent'])
            ->whereNumber('inquiry')
            ->name('supplier-inquiries.mark-sent');
        Route::post('/consultas-fornecedores/{inquiry}/respostas', [AdminSupplierInquiryController::class, 'storeResponse'])
            ->whereNumber('inquiry')
            ->name('supplier-inquiries.responses.store');

        Route::prefix('solicitacoes')->name('requests.')->group(function (): void {
            Route::get('/', [AdminQuoteRequestController::class, 'index'])->name('index');
            Route::get('/{quoteRequest}', [AdminQuoteRequestController::class, 'show'])
                ->whereNumber('quoteRequest')
                ->name('show');
            Route::patch('/{quoteRequest}/estado', [AdminQuoteRequestController::class, 'updateStatus'])
                ->whereNumber('quoteRequest')
                ->name('status.update');
        });

        Route::post('/pedidos/{order}/proformas', [AdminProformaController::class, 'storeForOrder'])
            ->whereNumber('order')
            ->name('orders.proformas.store');
        Route::get('/proformas/{proforma}', [AdminProformaController::class, 'show'])
            ->whereNumber('proforma')
            ->name('proformas.show');

        Route::prefix('pedidos')->name('orders.')->group(function (): void {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])
                ->whereNumber('order')
                ->name('show');
            Route::patch('/{order}/estado', [AdminOrderController::class, 'updateStatus'])
                ->whereNumber('order')
                ->name('status.update');
        });
    });
});
