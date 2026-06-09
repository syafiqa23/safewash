<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminIntegrationController;
use App\Http\Controllers\AdminSettlementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CustomerTrackingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaundryOrderController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MerchantCustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MerchantQrController;
use App\Http\Controllers\MerchantRevenueController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'landing'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.perform');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/track/{code}', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/webhooks/payments/midtrans', [PaymentWebhookController::class, 'midtrans'])->name('webhooks.payments.midtrans');
Route::post('/webhooks/payments/xendit', [PaymentWebhookController::class, 'xendit'])->name('webhooks.payments.xendit');
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])->name('webhooks.whatsapp.receive');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [LaundryOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [LaundryOrderController::class, 'create'])->middleware('role:admin,merchant')->name('orders.create');
    Route::get('/orders/{order}', [LaundryOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [LaundryOrderController::class, 'store'])->middleware('role:admin,merchant')->name('orders.store');
    Route::post('/orders/{order}/status', [LaundryOrderController::class, 'updateStatus'])->middleware('role:admin,merchant')->name('orders.status');
    Route::post('/orders/{order}/payment-link', [LaundryOrderController::class, 'createPaymentLink'])->middleware('role:admin,merchant')->name('orders.payment-link');
    Route::post('/orders/{order}/settle-payment', [LaundryOrderController::class, 'settlePayment'])->middleware('role:admin,merchant')->name('orders.settle-payment');
    Route::post('/orders/{order}/delivery', [LaundryOrderController::class, 'updateDelivery'])->middleware('role:admin,merchant')->name('orders.delivery');

    // Claims
    Route::post('/orders/{order}/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/create/{order}', [ClaimController::class, 'create'])->name('claims.create');

    // Loyalty
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');

    // Marketplace (customer)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/{laundry:slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
    Route::get('/marketplace/{laundry:slug}/order', [CustomerOrderController::class, 'create'])->name('customer.order.create');
    Route::post('/marketplace/{laundry:slug}/order', [CustomerOrderController::class, 'store'])->name('customer.order.store');
    Route::get('/orders/{order}/checkout', [CustomerOrderController::class, 'checkout'])->name('customer.order.checkout');
    Route::post('/orders/{order}/generate-payment-link', [CustomerOrderController::class, 'generatePaymentLink'])->name('customer.order.generate-link');
    Route::post('/orders/{order}/simulate-payment', [CustomerOrderController::class, 'simulatePayment'])->name('customer.order.simulate-payment');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Customer tracking (halaman dedicated, bukan prompt())
    Route::get('/my-tracking', [CustomerTrackingController::class, 'index'])->name('customer.tracking');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/bell', [NotificationController::class, 'bell'])->name('notifications.bell');

    // Merchant-specific
    Route::middleware('role:merchant')->group(function (): void {
        Route::get('/qr-manage', [MerchantQrController::class, 'index'])->name('merchant.qr.index');
        Route::get('/customers', [MerchantCustomerController::class, 'index'])->name('merchant.customers.index');
        Route::get('/revenue', [MerchantRevenueController::class, 'index'])->name('merchant.revenue.index');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/merchants', [AdminController::class, 'merchants'])->name('merchants.index');
    Route::get('/integrations', [AdminIntegrationController::class, 'index'])->name('integrations.index');
    Route::post('/integrations', [AdminIntegrationController::class, 'update'])->name('integrations.update');
    Route::patch('/merchants/{laundry}/toggle-status', [AdminController::class, 'toggleMerchantStatus'])->name('merchants.toggle-status');
    Route::post('/merchants/{laundry}', [AdminController::class, 'updateMerchant'])->name('merchants.update');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
    Route::get('/reports/excel', [AdminController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [AdminController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/claims', [AdminController::class, 'claims'])->name('claims.index');
    Route::patch('/claims/{claim}', [AdminController::class, 'updateClaim'])->name('claims.update');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments.index');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/settlement', [AdminSettlementController::class, 'index'])->name('settlement.index');
    Route::post('/settlement', [AdminSettlementController::class, 'store'])->name('settlement.store');
    Route::patch('/settlement/{settlement}', [AdminSettlementController::class, 'markPaid'])->name('settlement.mark-paid');
});
