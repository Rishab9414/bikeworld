<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\HomeReelController;
use App\Http\Controllers\Admin\HomeThemeController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\Masters\BikeModelController;
use App\Http\Controllers\Admin\Masters\BrandController;
use App\Http\Controllers\Admin\Masters\CategoryController;
use App\Http\Controllers\Admin\Masters\ColorController;
use App\Http\Controllers\Admin\Masters\ManufacturerController;
use App\Http\Controllers\Admin\Masters\MaterialController;
use App\Http\Controllers\Admin\Masters\SizeController;
use App\Http\Controllers\Admin\Masters\SupplierController;
use App\Http\Controllers\Admin\Masters\TaxController;
use App\Http\Controllers\Admin\Masters\UnitController;
use App\Http\Controllers\Admin\Masters\VehicleBrandController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoPopupController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StoreSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('admin.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Reports
    Route::middleware('permission:reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{type}/export', [ReportController::class, 'export'])->name('export');
        Route::get('/{type}', [ReportController::class, 'show'])->name('show');
    });

    // User Management
    Route::middleware('permission:users')->prefix('users')->name('users.')->group(function () {
        Route::get('/admin-users', [AdminUserController::class, 'index'])->name('admin-users.index');
        Route::get('/admin-users/data', [AdminUserController::class, 'data'])->name('admin-users.data');
        Route::get('/admin-users/roles-list', [AdminUserController::class, 'rolesList'])->name('admin-users.roles-list');
        Route::get('/admin-users/{id}', [AdminUserController::class, 'show'])->name('admin-users.show');
        Route::post('/admin-users', [AdminUserController::class, 'store'])->name('admin-users.store');
        Route::put('/admin-users/{id}', [AdminUserController::class, 'update'])->name('admin-users.update');
        Route::delete('/admin-users/{id}', [AdminUserController::class, 'destroy'])->name('admin-users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::get('/roles/permissions-list', [RoleController::class, 'permissionsList'])->name('roles.permissions-list');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::patch('/roles/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('permissions.data');
        Route::get('/permissions/{id}', [PermissionController::class, 'show'])->name('permissions.show');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        Route::get('/login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');
        Route::get('/login-history/data', [LoginHistoryController::class, 'data'])->name('login-history.data');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data');
    });

    // Orders
    Route::middleware('permission:orders')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/data', [OrderController::class, 'data'])->name('data');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/confirm', [OrderController::class, 'confirm'])->name('confirm');
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/save-shipment', [OrderController::class, 'saveShipment'])->name('save-shipment');
        Route::post('/{order}/generate-invoice', [OrderController::class, 'generateInvoice'])->name('generate-invoice');
        Route::post('/{order}/generate-label', [OrderController::class, 'generateLabel'])->name('generate-label');
        Route::get('/{order}/invoice', [OrderController::class, 'printInvoice'])->name('invoice');
        Route::get('/{order}/label', [OrderController::class, 'printLabel'])->name('label');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
        Route::post('/{order}/return', [OrderController::class, 'returnOrder'])->name('return');
    });

    // Customers
    Route::middleware('permission:customers')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/data', [CustomerController::class, 'data'])->name('data');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::patch('/{customer}/toggle-block', [CustomerController::class, 'toggleBlock'])->name('toggle-block');
        Route::post('/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('reset-password');
        Route::patch('/{customer}/verify-email', [CustomerController::class, 'verifyEmail'])->name('verify-email');
        Route::patch('/{customer}/verify-mobile', [CustomerController::class, 'verifyMobile'])->name('verify-mobile');
        Route::post('/{customer}/wallet', [CustomerController::class, 'walletAdjust'])->name('wallet');
        Route::post('/{customer}/loyalty', [CustomerController::class, 'loyaltyAdjust'])->name('loyalty');
    });

    // Homepage Banners
    Route::middleware('permission:marketing')->prefix('banners')->name('banners.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/create', [BannerController::class, 'create'])->name('create');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('edit');
        Route::put('/{banner}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy');
    });

    // Homepage Promo Popup (festival / offer)
    Route::middleware('permission:marketing')->prefix('promo-popups')->name('promo-popups.')->group(function () {
        Route::get('/', [PromoPopupController::class, 'index'])->name('index');
        Route::get('/create', [PromoPopupController::class, 'create'])->name('create');
        Route::post('/', [PromoPopupController::class, 'store'])->name('store');
        Route::get('/{promoPopup}/edit', [PromoPopupController::class, 'edit'])->name('edit');
        Route::put('/{promoPopup}', [PromoPopupController::class, 'update'])->name('update');
        Route::delete('/{promoPopup}', [PromoPopupController::class, 'destroy'])->name('destroy');
    });

    // Homepage Themes (festival / seasonal)
    Route::middleware('permission:marketing')->prefix('home-themes')->name('home-themes.')->group(function () {
        Route::get('/', [HomeThemeController::class, 'index'])->name('index');
        Route::get('/create', [HomeThemeController::class, 'create'])->name('create');
        Route::post('/', [HomeThemeController::class, 'store'])->name('store');
        Route::get('/{homeTheme}/edit', [HomeThemeController::class, 'edit'])->name('edit');
        Route::put('/{homeTheme}', [HomeThemeController::class, 'update'])->name('update');
        Route::delete('/{homeTheme}', [HomeThemeController::class, 'destroy'])->name('destroy');
    });

    // Homepage Reels
    Route::middleware('permission:marketing')->prefix('home-reels')->name('home-reels.')->group(function () {
        Route::get('/', [HomeReelController::class, 'index'])->name('index');
        Route::get('/create', [HomeReelController::class, 'create'])->name('create');
        Route::post('/', [HomeReelController::class, 'store'])->name('store');
        Route::get('/{homeReel}/edit', [HomeReelController::class, 'edit'])->name('edit');
        Route::put('/{homeReel}', [HomeReelController::class, 'update'])->name('update');
        Route::delete('/{homeReel}', [HomeReelController::class, 'destroy'])->name('destroy');
    });

    // Coupons
    Route::middleware('permission:marketing')->prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::put('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
    });

    // Store Announcements (top bar + ticker)
    Route::middleware('permission:marketing')->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    // Blog (SEO content)
    Route::middleware('permission:marketing')->prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{post}', [BlogController::class, 'destroy'])->name('destroy');
    });

    // Store Settings
    Route::middleware('permission:settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/payments', [StoreSettingsController::class, 'payments'])->name('payments');
        Route::put('/payments', [StoreSettingsController::class, 'updatePayments'])->name('payments.update');
        Route::get('/tax', [StoreSettingsController::class, 'tax'])->name('tax');
        Route::put('/tax', [StoreSettingsController::class, 'updateTax'])->name('tax.update');
        Route::get('/homepage', [StoreSettingsController::class, 'homepage'])->name('homepage');
        Route::put('/homepage', [StoreSettingsController::class, 'updateHomepage'])->name('homepage.update');
        Route::get('/maintenance', [StoreSettingsController::class, 'maintenance'])->name('maintenance');
        Route::put('/maintenance', [StoreSettingsController::class, 'updateMaintenance'])->name('maintenance.update');
    });

    // Products
    Route::middleware('permission:products')->prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/data', [ProductController::class, 'data'])->name('data');
        Route::get('/options', [ProductController::class, 'options'])->name('options');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product:id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product:id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product:id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Master Data CRUD routes
    $masters = [
        'categories' => CategoryController::class,
        'brands' => BrandController::class,
        'manufacturers' => ManufacturerController::class,
        'suppliers' => SupplierController::class,
        'taxes' => TaxController::class,
        'units' => UnitController::class,
        'sizes' => SizeController::class,
        'colors' => ColorController::class,
        'materials' => MaterialController::class,
        'vehicle-brands' => VehicleBrandController::class,
        'bike-models' => BikeModelController::class,
    ];

    foreach ($masters as $uri => $controller) {
        $name = str_replace('-', '_', $uri);
        Route::middleware('permission:masters')->prefix("masters/{$uri}")->name("masters.{$name}.")->group(function () use ($controller) {
            Route::get('/', [$controller, 'index'])->name('index');
            Route::get('/data', [$controller, 'data'])->name('data');
            Route::get('/{id}', [$controller, 'show'])->name('show');
            Route::post('/', [$controller, 'store'])->name('store');
            Route::put('/{id}', [$controller, 'update'])->name('update');
            Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [$controller, 'toggleStatus'])->name('toggle-status');
        });
    }
});
