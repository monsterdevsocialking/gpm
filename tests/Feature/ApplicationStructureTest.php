<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminQuoteRequestController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CustomerQuoteRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\QuoteCartController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Middleware\EnsureCustomerIsActive;
use App\Http\Middleware\EnsureStaffIsActive;
use App\Http\Middleware\EnsureStaffRole;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\QuoteRequestStatusHistory;
use Tests\TestCase;

class ApplicationStructureTest extends TestCase
{
    public function test_http_controllers_and_supply_request_models_are_autoloadable(): void
    {
        foreach ([
            HomeController::class,
            MarketplaceController::class,
            ProductMediaController::class,
            QuoteCartController::class,
            CustomerAuthController::class,
            CustomerPortalController::class,
            CustomerOrderController::class,
            CustomerQuoteRequestController::class,
            StaffAuthController::class,
            AdminDashboardController::class,
            AdminOrderController::class,
            AdminQuoteRequestController::class,
            EnsureCustomerIsActive::class,
            EnsureStaffIsActive::class,
            EnsureStaffRole::class,
            QuoteRequest::class,
            QuoteRequestItem::class,
            QuoteRequestStatusHistory::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), "Expected {$class} to autoload.");
        }
    }
}
