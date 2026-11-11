<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminConversationController;
use App\Http\Controllers\CustomerConversationController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminStockController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProformaController;
use App\Http\Controllers\AdminProposalController;
use App\Http\Controllers\AdminQuoteRequestController;
use App\Http\Controllers\AdminSupplierController;
use App\Http\Controllers\AdminSupplierInquiryController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CustomerProposalController;
use App\Http\Controllers\CustomerQuoteRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\QuoteCartController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Middleware\EnsureCustomerIsActive;
use App\Http\Middleware\EnsureStaffIsActive;
use App\Http\Middleware\EnsureStaffRole;
use App\Models\ContactMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\IntegrationEvent;
use App\Models\Proforma;
use App\Models\Proposal;
use App\Models\ProposalDecision;
use App\Models\ProposalItem;
use App\Models\ProposalVersion;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\QuoteRequestStatusHistory;
use App\Models\Supplier;
use App\Services\Integrations\IntegrationEventRecorder;
use App\Models\SupplierInquiry;
use App\Models\SupplierResponse;
use App\Models\SupplierResponseItem;
use Tests\TestCase;

class ApplicationStructureTest extends TestCase
{
    public function test_http_controllers_and_domain_models_are_autoloadable(): void
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
            CustomerProposalController::class,
            StaffAuthController::class,
            AdminDashboardController::class,
            AdminActivityLogController::class,
            AdminCompanyController::class,
            AdminUserController::class,
            AdminCustomerController::class,
            AdminContactController::class,
            AdminConversationController::class,
            CustomerConversationController::class,
            AdminCatalogController::class,
            AdminCategoryController::class,
            AdminStockController::class,
            AdminOrderController::class,
            AdminProformaController::class,
            AdminQuoteRequestController::class,
            AdminSupplierController::class,
            AdminSupplierInquiryController::class,
            AdminProposalController::class,
            EnsureCustomerIsActive::class,
            EnsureStaffIsActive::class,
            EnsureStaffRole::class,
            QuoteRequest::class,
            QuoteRequestItem::class,
            QuoteRequestStatusHistory::class,
            Supplier::class,
            SupplierInquiry::class,
            SupplierResponse::class,
            SupplierResponseItem::class,
            Proforma::class,
            Proposal::class,
            ProposalVersion::class,
            ProposalItem::class,
            ProposalDecision::class,
            IntegrationEvent::class,
            ContactMessage::class,
            Conversation::class,
            Message::class,
            IntegrationEventRecorder::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), "Expected {$class} to autoload.");
        }
    }
}
