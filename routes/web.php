<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\CourierScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UserPackageController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\CourierZoneController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public tracking (with rate limiting - relaxed in non-production)
if (app()->environment('production')) {
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/track', [TrackingController::class, 'index'])->name('tracking.index');
        Route::post('/track', [TrackingController::class, 'track'])->name('tracking.track');
    });
} else {
    Route::get('/track', [TrackingController::class, 'index'])->name('tracking.index');
    Route::post('/track', [TrackingController::class, 'track'])->name('tracking.track');
}

// Public API (read-only, API key required)
Route::prefix('api/v1')->middleware([\App\Http\Middleware\AuthenticateApiKey::class])->group(function () {
    Route::get('/track/{resi}', [\App\Http\Controllers\PublicApiController::class, 'track'])->name('api.track');
});

// Midtrans callback (public route, no auth required, but IP validated)
Route::post('/admin/payments/midtrans-callback', [\App\Http\Controllers\PaymentController::class, 'midtransCallback'])
    ->middleware('validate.payment.ip')
    ->name('admin.payments.midtrans-callback');

// Monitoring endpoints (public for health checks, admin for detailed metrics)
Route::get('/health', [\App\Http\Controllers\MonitoringController::class, 'health'])->name('health');
Route::get('/health/queue', [\App\Http\Controllers\MonitoringController::class, 'queueHealth'])->name('health.queue');
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/monitoring/metrics', [\App\Http\Controllers\MonitoringController::class, 'metrics'])->name('monitoring.metrics');
    Route::get('/monitoring/failed-jobs', [\App\Http\Controllers\MonitoringController::class, 'failedJobs'])->name('monitoring.failed-jobs');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    
    if (app()->environment('production')) {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    } else {
        Route::post('/login', [AuthController::class, 'login']);
    }

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    
    if (app()->environment('production')) {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    } else {
        Route::post('/register', [AuthController::class, 'register']);
    }

    Route::get('/register/verify', [AuthController::class, 'showVerifyForm'])->name('register.verify');
    Route::post('/register/verify', [AuthController::class, 'verify'])->name('register.verify.submit');
    Route::post('/register/resend-verification', [AuthController::class, 'resendVerificationCode'])->name('register.resend-verification');
    
    // Password Reset Routes
    Route::get('/password/forgot', [\App\Http\Controllers\PasswordResetController::class, 'showForgotPasswordForm'])->name('password.forgot');

    if (app()->environment('production')) {
        Route::post('/password/email', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])
            ->middleware('throttle:3,60')
            ->name('password.email');
    } else {
        Route::post('/password/email', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])
            ->name('password.email');
    }
    Route::get('/password/reset-code', [\App\Http\Controllers\PasswordResetController::class, 'showResetCodeForm'])->name('password.reset.code');
    Route::post('/password/verify-code', [\App\Http\Controllers\PasswordResetController::class, 'verifyResetCode'])->name('password.verify.code');
    Route::get('/password/reset/{token?}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});

    Route::middleware(['auth', 'active'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Profile routes (all authenticated users)
        Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Owner routes (all features)
    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/settings/fonnte', [OwnerController::class, 'settingsFonnte'])->name('settings.fonnte');
        Route::put('/settings/fonnte', [OwnerController::class, 'updateSettingsFonnte'])->name('settings.fonnte.update');
        Route::get('/settings/midtrans', [OwnerController::class, 'settingsMidtrans'])->name('settings.midtrans');
        Route::put('/settings/midtrans', [OwnerController::class, 'updateSettingsMidtrans'])->name('settings.midtrans.update');
    });
    
    // Manager Dashboard
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');
    
    // Admin & Owner routes (shared)
    Route::middleware('admin_or_owner')->prefix('admin')->name('admin.')->group(function () {
        // Custom routes must be defined before resource routes to avoid conflicts
        Route::get('shipments/assign/form', [ShipmentController::class, 'assignForm'])->name('shipments.assign.form');
        Route::post('shipments/assign', [ShipmentController::class, 'assign'])->name('shipments.assign');
        Route::get('shipments/pricing/get', [ShipmentController::class, 'getPricing'])->name('shipments.pricing.get');
        Route::post('shipments/{shipmentId}/send-notification', [ShipmentController::class, 'sendNotification'])->name('shipments.send-notification');
        
        Route::resource('shipments', ShipmentController::class);
        Route::get('shipments/{shipmentId}/edit-status', [ShipmentController::class, 'editStatus'])->name('shipments.edit-status');
        Route::post('shipments/{shipmentId}/update-status', [ShipmentController::class, 'updateStatus'])->name('shipments.update-status');
        Route::get('shipments/{shipmentId}/print', [ShipmentController::class, 'printResi'])->name('shipments.print');
        
        // Payment routes
        Route::post('shipments/{shipment}/payment/cash', [\App\Http\Controllers\PaymentController::class, 'processCashPayment'])->name('shipments.payment.cash');
        Route::post('shipments/{shipment}/payment/qris', [\App\Http\Controllers\PaymentController::class, 'createQrisPayment'])->name('shipments.payment.qris');
        Route::get('shipments/{shipment}/payment/status', [\App\Http\Controllers\PaymentController::class, 'checkPaymentStatus'])->name('shipments.payment.status');
        Route::get('shipments/{shipment}/payment/qr-image', [\App\Http\Controllers\PaymentController::class, 'getQrCodeImage'])->name('shipments.payment.qr-image');
        Route::get('shipments/{shipment}/payment/detail', [\App\Http\Controllers\PaymentController::class, 'showPaymentDetail'])->name('shipments.payment.detail');
        
        // Payment management routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentController::class, 'listPayments'])->name('index');
            Route::get('/failed', [\App\Http\Controllers\PaymentController::class, 'listFailedPayments'])->name('failed');
        });
        
        // Management Data
        Route::resource('branches', BranchController::class);
        Route::get('branches/{branch}/users', [BranchController::class, 'show'])->name('branches.users');
        Route::post('branches/{branch}/assign-users', [BranchController::class, 'assignUsers'])->name('branches.assign-users');
        Route::delete('branches/{branch}/users/{user}', [BranchController::class, 'removeUser'])->name('branches.remove-user');
        Route::resource('kurirs', KurirController::class);
        Route::resource('pricing', PricingController::class);
        Route::resource('expeditions', ExpeditionController::class)->except(['show']);

        // User Management (Owner can CRUD, Manager can only view)
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        
        // User Management CRUD (Owner only) - must be before {user} route to avoid conflict
        Route::middleware('owner')->group(function () {
            Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        });
        
        // User show (Owner and Manager can view)
        Route::get('users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        
        // Zone Management
        Route::resource('zones', ZoneController::class);
        Route::get('couriers/{courier}/zones', [CourierZoneController::class, 'edit'])->name('couriers.zones.edit');
        Route::put('couriers/{courier}/zones', [CourierZoneController::class, 'update'])->name('couriers.zones.update');
        
        // Financial Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/cod', [ReportController::class, 'codReport'])->name('cod');
            Route::get('/cod/detail', [ReportController::class, 'codDetail'])->name('cod.detail');
            Route::get('/non-cod', [ReportController::class, 'nonCodReport'])->name('non-cod');
            Route::get('/non-cod/detail', [ReportController::class, 'nonCodDetail'])->name('non-cod.detail');
            Route::get('/courier-balance', [ReportController::class, 'courierBalance'])->name('courier-balance');
        });
        
        // Finance (All Financial Data)
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::get('/settlements/create', [FinanceController::class, 'createSettlement'])->name('settlements.create');
            Route::post('/settlements', [FinanceController::class, 'storeSettlement'])->name('settlements.store');
            Route::get('/settlements/{settlement}', [FinanceController::class, 'showSettlement'])->name('settlements.show');
            Route::post('/settlements/{settlement}/confirm', [FinanceController::class, 'confirmSettlement'])->name('settlements.confirm');
        });
        
        // Performance Dashboards
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/manager', [PerformanceController::class, 'managerDashboard'])->name('manager');
            Route::get('/admin', [PerformanceController::class, 'adminDashboard'])->name('admin');
        });
        
        // Notification Templates (Owner only)
        Route::middleware('owner')->prefix('notification-templates')->name('notification-templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\NotificationTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\NotificationTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\NotificationTemplateController::class, 'store'])->name('store');
            Route::get('/{notificationTemplate}/edit', [\App\Http\Controllers\NotificationTemplateController::class, 'edit'])->name('edit');
            Route::put('/{notificationTemplate}', [\App\Http\Controllers\NotificationTemplateController::class, 'update'])->name('update');
            Route::delete('/{notificationTemplate}', [\App\Http\Controllers\NotificationTemplateController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Manager routes
    Route::middleware(['auth', 'active', 'manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        
        // Performance Dashboards (read-only for manager)
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/manager', [PerformanceController::class, 'managerDashboard'])->name('manager');
        });
        
        // Data Barang Keluar Masuk
        Route::get('/barang-keluar-masuk', [ManagerDashboardController::class, 'barangKeluarMasuk'])->name('barang-keluar-masuk');
        
        // Financial Reports (read-only for manager)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/cod', [ReportController::class, 'codReport'])->name('cod');
            Route::get('/cod/detail', [ReportController::class, 'codDetail'])->name('cod.detail');
            Route::get('/non-cod', [ReportController::class, 'nonCodReport'])->name('non-cod');
            Route::get('/non-cod/detail', [ReportController::class, 'nonCodDetail'])->name('non-cod.detail');
        });
        
        // Finance (read-only for manager)
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::get('/settlements/{settlement}', [FinanceController::class, 'showSettlement'])->name('settlements.show');
        });
        
        // Zones (read-only for manager)
        Route::get('zones', [ZoneController::class, 'index'])->name('zones.index');
        Route::get('zones/{zone}', [ZoneController::class, 'show'])->name('zones.show');
    });
    
    // Kurir routes
    Route::middleware('kurir')->prefix('courier')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierController::class, 'dashboard'])->name('dashboard');
        Route::get('/performance', [PerformanceController::class, 'courierDashboard'])->name('performance');
        Route::post('/shipments/{shipment}/update-status', [CourierController::class, 'updateStatus'])->name('shipments.update-status');
        Route::get('/shipments', [CourierController::class, 'getShipments'])->name('shipments.list');
        
        // Scan Resi - Ambil Paket
        Route::get('/scan', [CourierScanController::class, 'scanForm'])->name('scan');
        Route::post('/scan', [CourierScanController::class, 'scanResi'])->name('scan.resi');
        Route::get('/my-packages', [CourierScanController::class, 'myPackages'])->name('my-packages');
        Route::get('/my-packages/{shipment}', [CourierScanController::class, 'show'])->name('my-packages.show');
        Route::post('/my-packages/bulk-update-status', [CourierScanController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        
        // Bayar COD QRIS
        Route::post('/shipments/{shipment}/payment/qris', [\App\Http\Controllers\PaymentController::class, 'createQrisPayment'])->name('shipments.payment.qris');
        Route::post('/shipments/{shipment}/payment/cash', [\App\Http\Controllers\PaymentController::class, 'processCashPayment'])->name('shipments.payment.cash');
    });
    
    // User routes
    Route::middleware(['auth', 'active'])->prefix('user')->name('user.')->group(function () {
        Route::get('/packages', [UserPackageController::class, 'history'])->name('packages.history');
        Route::get('/packages/{shipment}', [UserPackageController::class, 'show'])->name('packages.show');
        Route::post('/packages/{shipment}/review', [UserPackageController::class, 'review'])->name('packages.review');
    });
});
