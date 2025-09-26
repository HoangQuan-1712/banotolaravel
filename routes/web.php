<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewResponseController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\User\OrderController as UserOrderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $brands = \App\Models\Category::query()
        ->orderBy('name')
        ->get(['id','name']);

    $featuredProducts = \App\Models\Product::query()
        ->orderByDesc('price')
        ->take(8)
        ->get();

    // Mark top 3 as VIP by price (can be adjusted later)
    $vipIds = $featuredProducts->take(3)->pluck('id')->toArray();

    return view('welcome', [
        'brands' => $brands,
        'featuredProducts' => $featuredProducts,
        'vipIds' => $vipIds,
    ]);
})->name('welcome');


// Auth routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public routes (guest/customer: chỉ index/show)
Route::resource('categories', CategoryController::class)->only(['index', 'show']);
Route::resource('products', ProductController::class)->only(['index', 'show']);

// Hiển thị thông báo xác thực email
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Xử lý link xác nhận (từ email)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('welcome'); // Redirect về trang chào mừng
})->middleware(['auth', 'signed'])->name('verification.verify');

// Gửi lại email xác nhận
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// Admin routes (middleware admin)
Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('categories', [AdminController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [AdminController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [AdminController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/{category}', [AdminController::class, 'show'])->name('admin.categories.show');
    Route::get('categories/{category}/edit', [AdminController::class, 'edit'])->name('admin.categories.edit');
    Route::patch('categories/{category}', [AdminController::class, 'update'])->name('admin.categories.update');
    Route::delete('categories/{category}', [AdminController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('products', [AdminController::class, 'productIndex'])->name('admin.products.index');
    Route::get('products/create', [AdminController::class, 'productCreate'])->name('admin.products.create');
    Route::post('products', [AdminController::class, 'productStore'])->name('admin.products.store');
    Route::get('products/{product}', [AdminController::class, 'productShow'])->name('admin.products.show');
    Route::get('products/{product}/edit', [AdminController::class, 'productEdit'])->name('admin.products.edit');
    Route::patch('products/{product}', [AdminController::class, 'productUpdate'])->name('admin.products.update');
    Route::delete('products/{product}', [AdminController::class, 'productDestroy'])->name('admin.products.destroy');

    // Admin User Management Routes
    Route::get('users/statistics', [\App\Http\Controllers\Admin\UserController::class, 'statistics'])->name('admin.users.statistics');
    Route::get('users/{user}/delete-confirmation', [\App\Http\Controllers\Admin\UserController::class, 'deleteConfirmation'])->name('admin.users.delete-confirmation');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');

    // Admin Order Management Routes
    Route::get('orders', [App\Http\Controllers\AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/create', [App\Http\Controllers\AdminOrderController::class, 'create'])->name('admin.orders.create');
    Route::post('orders', [App\Http\Controllers\AdminOrderController::class, 'store'])->name('admin.orders.store');
    Route::get('orders/statistics', [App\Http\Controllers\AdminOrderController::class, 'statistics'])->name('admin.orders.statistics');
    Route::get('orders/search', [App\Http\Controllers\AdminOrderController::class, 'search'])->name('admin.orders.search');
    Route::get('orders/status/{status}', [App\Http\Controllers\AdminOrderController::class, 'byStatus'])->name('admin.orders.by-status');
    Route::get('orders/{order}', [App\Http\Controllers\AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::get('orders/{order}/edit', [App\Http\Controllers\AdminOrderController::class, 'edit'])->name('admin.orders.edit');
    Route::patch('orders/{order}', [App\Http\Controllers\AdminOrderController::class, 'update'])->name('admin.orders.update');
    Route::patch('orders/{order}/status', [App\Http\Controllers\AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::delete('orders/{order}', [App\Http\Controllers\AdminOrderController::class, 'destroy'])->name('admin.orders.destroy');
    Route::get('orders/export/report', [App\Http\Controllers\AdminOrderController::class, 'exportReport'])->name('admin.orders.export-report');
    Route::get('orders/export/pdf', [App\Http\Controllers\AdminOrderController::class, 'exportPdf'])->name('admin.orders.export-pdf');

    // Admin Dashboard Routes
    Route::get('dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard.index');
    
    // Admin Review Management Routes
    Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('admin.reviews.show');
    Route::patch('reviews/{review}/status', [App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('admin.reviews.update-status');
    Route::post('reviews/{review}/respond', [App\Http\Controllers\Admin\ReviewController::class, 'respond'])->name('admin.reviews.respond');
    Route::delete('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::get('products/{product}/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'byProduct'])->name('admin.reviews.by-product');
    Route::get('dashboard/reports', [App\Http\Controllers\AdminDashboardController::class, 'reports'])->name('admin.dashboard.reports');
    Route::get('dashboard/settings', [App\Http\Controllers\AdminDashboardController::class, 'settings'])->name('admin.dashboard.settings');
    Route::post('dashboard/settings', [App\Http\Controllers\AdminDashboardController::class, 'updateSettings'])->name('admin.dashboard.update-settings');

    // Admin Voucher Management
    Route::resource('vouchers', \App\Http\Controllers\Admin\VoucherAdminController::class)->names('admin.vouchers');


    // Admin System Management Routes
    Route::get('system/overview', [App\Http\Controllers\AdminSystemController::class, 'overview'])->name('admin.system.overview');
    Route::get('system/database', [App\Http\Controllers\AdminSystemController::class, 'database'])->name('admin.system.database');
    Route::post('system/database/backup', [App\Http\Controllers\AdminSystemController::class, 'backupDatabase'])->name('admin.system.backup-database');
    Route::get('system/storage', [App\Http\Controllers\AdminSystemController::class, 'storage'])->name('admin.system.storage');
    Route::post('system/storage/cleanup', [App\Http\Controllers\AdminSystemController::class, 'cleanupStorage'])->name('admin.system.cleanup-storage');
    Route::get('system/cache', [App\Http\Controllers\AdminSystemController::class, 'cache'])->name('admin.system.cache');
    Route::post('system/cache/clear', [App\Http\Controllers\AdminSystemController::class, 'clearCache'])->name('admin.system.clear-cache');
    Route::get('system/logs', [App\Http\Controllers\AdminSystemController::class, 'logs'])->name('admin.system.logs');
    Route::get('system/logs/{filename}', [App\Http\Controllers\AdminSystemController::class, 'viewLog'])->name('admin.system.view-log');
    Route::delete('system/logs/{filename}', [App\Http\Controllers\AdminSystemController::class, 'deleteLog'])->name('admin.system.delete-log');
    Route::get('system/security', [App\Http\Controllers\AdminSystemController::class, 'security'])->name('admin.system.security');
    Route::post('system/users/{user}/ban', [App\Http\Controllers\AdminSystemController::class, 'banUser'])->name('admin.system.ban-user');
    Route::post('system/users/{user}/unban', [App\Http\Controllers\AdminSystemController::class, 'unbanUser'])->name('admin.system.unban-user');

    // Admin Activity Tracking Routes
    Route::get('activities', [App\Http\Controllers\AdminActivityController::class, 'index'])->name('admin.activities.index');
    Route::get('activities/user/{user}', [App\Http\Controllers\AdminActivityController::class, 'userActivity'])->name('admin.activities.user-activity');
    Route::get('activities/product/{product}', [App\Http\Controllers\AdminActivityController::class, 'productActivity'])->name('admin.activities.product-activity');
    Route::get('activities/reports', [App\Http\Controllers\AdminActivityController::class, 'reports'])->name('admin.activities.reports');
    Route::post('activities/export', [App\Http\Controllers\AdminActivityController::class, 'exportReport'])->name('admin.activities.export');
});

// Customer: get or create current user's chat (used by widget to obtain chat_id)
Route::middleware(['auth'])->get('/chat/my', [ChatController::class, 'open'])->name('chat.my');
Route::prefix('user')->name('user.')->group(function () {
    Route::resource('categories', CategoryController::class);
});

// Removed guest-accessible cart routes to ensure only authenticated users can purchase

// Cart routes (require verified email for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// Admin chat console (admins do NOT need verified email)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/chats', [ChatController::class, 'adminIndex'])->name('admin.chats.index');
    Route::get('/admin/chats/{chat}', [ChatController::class, 'adminShow'])->name('admin.chats.show');
    Route::post('/admin/chats/{chat}/assign', [ChatController::class, 'assign'])->name('admin.chats.assign');
    Route::post('/admin/chats/{chat}/close',  [ChatController::class, 'close'])->name('admin.chats.close');
});

// Chat messages API for both user widget and admin console
Route::middleware(['auth'])->group(function () {
    Route::get('/chat/{chat}/messages', [\App\Http\Controllers\MessageController::class, 'index'])
        ->name('chat.messages.index');
    Route::post('/chat/{chat}/messages', [\App\Http\Controllers\MessageController::class, 'store'])
        ->name('chat.messages.store');
    Route::post('/chat/{chat}/typing', [\App\Http\Controllers\MessageController::class, 'typing'])
        ->name('chat.typing');
});

// Thanh toán (OrderController xử lý cả COD & MoMo) - require verified email for users, admins bypass
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::get('/payment', [OrderController::class, 'index'])->name('user.payment.index');
    Route::post('/payment/process', [OrderController::class, 'processPayment'])->name('user.payment.process');
    // Thanh toán lại MoMo cho một đơn đã tạo
    Route::get('/orders/{order}/pay/momo', [OrderController::class, 'payAgain'])->name('user.orders.momo.pay');
    // Lịch sử đơn hàng và xem chi tiết
    Route::get('/orders', [OrderController::class, 'orderHistory'])->name('user.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('user.orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('user.orders.cancel');
    
    // Reviews (store from order context) - use distinct name to avoid conflicts
    Route::post('/reviews/store', [OrderController::class, 'storeReview'])->name('order.reviews.store');
});

// MoMo callback & IPN (không yêu cầu auth)
Route::get('/payment/momo/callback', [OrderController::class, 'callback'])->name('user.payment.momo.callback');
Route::post('/payment/momo/ipn', [OrderController::class, 'ipn'])->name('user.payment.momo.ipn');

// Wishlist routes (require verified email for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::get('/wishlist', [App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add/{product}', [App\Http\Controllers\WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{product}', [App\Http\Controllers\WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/wishlist/toggle/{product}', [App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/clear', [App\Http\Controllers\WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('/wishlist/move-to-cart/{product}', [App\Http\Controllers\WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
});

// Voucher routes (require verified email for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::get('/checkout/vouchers/{order}', [VoucherController::class,'showChoices'])->name('vouchers.choices');
    Route::post('/checkout/vouchers/{order}/apply', [VoucherController::class,'applyChoice'])->name('vouchers.apply');
    Route::post('/checkout/vouchers/{order}/random', [VoucherController::class,'randomGift'])->name('vouchers.random');
    // Specific voucher APIs must come BEFORE the wildcard '{order}' route to avoid 404
    Route::get('/api/vouchers/preview', [VoucherController::class,'preview'])->name('vouchers.preview');
    Route::get('/api/vouchers/preview-apply', [VoucherController::class,'previewApply'])->name('vouchers.preview-apply');
    Route::get('/api/vouchers/{order}', [VoucherController::class,'getAvailableVouchers'])->name('vouchers.api');
});

// Product Reviews routes (require verified email for users, admins bypass) - namespaced as 'product-reviews.*'
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::post('/products/{product}/reviews', [App\Http\Controllers\ProductReviewController::class, 'store'])->name('product-reviews.store');
    Route::put('/reviews/{review}', [App\Http\Controllers\ProductReviewController::class, 'update'])->name('product-reviews.update');
    Route::delete('/reviews/{review}', [App\Http\Controllers\ProductReviewController::class, 'destroy'])->name('product-reviews.destroy');
    Route::get('/my-reviews', [App\Http\Controllers\ProductReviewController::class, 'myReviews'])->name('product-reviews.my');
    Route::get('/products/{product}/reviews', [App\Http\Controllers\ProductReviewController::class, 'productReviews'])->name('product-reviews.index');
});

// Review routes (require verified email for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [App\Http\Controllers\ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
});

// User Account Management (require verified for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->prefix('account')->name('user.account.')->group(function () {
    Route::get('/', [\App\Http\Controllers\User\AccountController::class, 'dashboard'])->name('dashboard');
    Route::post('/profile', [\App\Http\Controllers\User\AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password', [\App\Http\Controllers\User\AccountController::class, 'changePassword'])->name('password.change');
});

// Search routes
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
Route::get('/search/advanced', [App\Http\Controllers\SearchController::class, 'advanced'])->name('search.advanced');
Route::get('/search/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/compare', [App\Http\Controllers\SearchController::class, 'compare'])->name('search.compare');

// User Address Management Routes (require verified email for users, admins bypass)
Route::middleware(['auth', 'verified_or_admin'])->group(function () {
    Route::get('/addresses', [App\Http\Controllers\UserAddressController::class, 'index'])->name('user.addresses.index');
    Route::get('/addresses/create', [App\Http\Controllers\UserAddressController::class, 'create'])->name('user.addresses.create');
    Route::post('/addresses', [App\Http\Controllers\UserAddressController::class, 'store'])->name('user.addresses.store');
    Route::get('/addresses/{address}', [App\Http\Controllers\UserAddressController::class, 'show'])->name('user.addresses.show');
    Route::get('/addresses/{address}/edit', [App\Http\Controllers\UserAddressController::class, 'edit'])->name('user.addresses.edit');
    Route::patch('/addresses/{address}', [App\Http\Controllers\UserAddressController::class, 'update'])->name('user.addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\UserAddressController::class, 'destroy'])->name('user.addresses.destroy');
    Route::patch('/addresses/{address}/set-default', [App\Http\Controllers\UserAddressController::class, 'setDefault'])->name('user.addresses.set-default');
    Route::get('/api/addresses', [App\Http\Controllers\UserAddressController::class, 'getAddresses'])->name('user.addresses.api');
});