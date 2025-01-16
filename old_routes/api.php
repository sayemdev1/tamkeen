<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\MembershipLevelController;
use App\Http\Controllers\NewslettersController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('login', [LoginController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);
Route::get('packages/{package}',[PackageController::class,'show']);


Route::get('products/{product}', [ProductController::class, 'show']);
// Group routes that require 'auth:api' middleware
Route::middleware('auth:api')->group(function () {


    //// change password  
    Route::post('change-password', [UserController::class, 'changePassword']);
    
    Route::resource('products', ProductController::class)->except(['show']);
    
    Route::post('membership-levels/{membershipLevel}/subscribe', [MembershipLevelController::class, 'subscribe']);

    Route::get('user-orders', [OrderController::class, 'userOrders']);
    Route::get('user-pending-orders', [OrderController::class, 'userPendingOrders']);
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancelOrderForUser']);
    
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('view-cart', [CartController::class, 'viewCart']);
    Route::post('/cart/checkout', [CheckoutController::class, 'checkout']);
    
    Route::get('getStoreCustomers', [CustomersController::class, 'getStoreCustomers']);
    Route::get('getTransactions', [TransactionController::class, 'getTransactions']);
    
    Route::get('getInvoices', [InvoicesController::class, 'getInvoices']);
    Route::get('orders/{order}/viewDetails', [InvoicesController::class, 'viewDetails']);
    
    Route::get('stores/getStats/{storeId?}',[StoreController::class, 'stats']);

    Route::get('stores/getDailyRevenue/{storeId?}',[StoreController::class, 'dailyRevenue']);

    Route::get('stores/getSalesPercentageByCountry/{storeId?}',[StoreController::class, 'salesPercentageByCountry']);
    
    Route::post('products/{product}/rate', [ProductController::class, 'rateProduct']);

    ///user profile
    Route::get('show-profile', [UserController::class, 'showForUser']);
    Route::put('update-profile', [UserController::class, 'updateForUser']);


    /// wishlist
    Route::get('wishlists', [WishlistController::class, 'getAll']);
    Route::get('wishlists/{wishlist}', [WishlistController::class, 'showForUser']);
    Route::post('wishlists/add', [WishlistController::class, 'add']);
    Route::delete('wishlists/remove', [WishlistController::class, 'remove']);


    Route::get('roles/{role}/permissions', [PermissionController::class, 'rolePermissions']);
    Route::get('user-permissions', [PermissionController::class, 'userPermissions']);

    Route::get('my-discount',[DiscountController::class, 'getMyDiscount']);
    Route::post('add-to-my-discount', [DiscountController::class, 'addToMyDiscount']);

    Route::post('referUser',[ReferralController::class, 'referUser']);
    Route::get('get-referrals-details',[ReferralController::class, 'referralDetails']);


    Route::post('storeByAdmin', [ChatGroupController::class, 'storeByAdmin']);
    Route::post('storeByUser', [ChatGroupController::class, 'storeByUser']);


    Route::get('chat-messages/{chatGroup}', [ChatMessageController::class, 'index']);
    Route::post('chat-messages/{chatGroup}/store', [ChatMessageController::class, 'store']);


    Route::get('chat-groups', [ChatGroupController::class, 'index']);
    Route::get('chat-groups/{chatGroup}', [ChatGroupController::class, 'show']);


    Route::post('baskets/{package}/checkout', [PackageController::class, 'checkoutForPackage']);

    

    ///// add address
    Route::post('add-address', [AddressController::class, 'store']);
    Route::get('addresses', [AddressController::class, 'index']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);
    Route::put('addresses/{address}', [AddressController::class, 'update']);

    ////payment
    Route::resource('payments', PaymentMethodsController::class)->only(['store','update','destroy']);


Route::get('/dashboard-best-selling-products', [ProductController::class, 'dashboardBestSelling']);
    Route::post('packages/{package}/add-review', [PackageController::class, 'reviewPackage']);

    Route::get('getOrdersOfPackagesForUser',[PackageController::class, 'getOrdersOfPackageForUser']);
    Route::get('getOrdersOfPackages/{storeId?}',[PackageController::class, 'getOrdersOfPackage']);
    Route::post('packages/{storeId?}', [PackageController::class, 'store']);

    Route::get('getReferralsWithProfits',[ReferralController::class, 'getReferralsWithProfits']);
    
    Route::resource('/packages', PackageController::class)->except(['store','show']);
    
});

Route::get('stores/{store}',[StoreController::class, 'show']);

// Routes without authentication
Route::post('orders/{order}/add-review', [OrderController::class, 'addReview']);
Route::resource('membership-levels', MembershipLevelController::class);
Route::get('show-products', [ProductController::class, 'showProducts']);
Route::get('stores/{store}/products', [ProductController::class, 'showProductsForStore']);
Route::get('Customers/{user}/Orders', [CustomersController::class, 'showCustomerOrders']);
Route::resource('users', UserController::class);
Route::resource('categories', CategoriesController::class);
Route::get('categories/{category}/orders', [CategoriesController::class, 'ordersOfCategory']);
Route::get('categories/{category}/products', [CategoriesController::class, 'showProducts']);
Route::get('categories/{category}/changeOrders', [CategoriesController::class, 'orderChangesOfCategory']);
Route::resource('companies', CompaniesController::class)->only(['index','store'])->middleware('auth:api');
Route::resource('stores', StoreController::class)->except('show');
Route::resource('permissions', PermissionController::class);
Route::resource('roles', RoleController::class);
Route::post('roles/{role}/permissions',[PermissionController::class, 'storeRolePermission']);
Route::put('orders/{order}/cancel', [OrderController::class, 'cancelOrder']);
Route::put('orders/{order}/approve', [OrderController::class, 'approveOrder']);
Route::put('orders/{order}/pending', [OrderController::class, 'pendingOrder']);
Route::put('orders/{order}/complete', [OrderController::class, 'completedOrder']);

Route::resource('settings',SettingController::class);
Route::get('/best-selling-products', [ProductController::class, 'bestSelling']);
Route::get('/last-products', [ProductController::class, 'getLastProducts']);
Route::get('/happyCustomers', [ProductController::class, 'happyCustomers']);
Route::get('/flash-sales', [ProductController::class, 'flashSale']);
Route::post('join-as-vendor',[RegisterController::class , 'joinAsVendor']);
Route::resource('newsletters', NewslettersController::class);
Route::resource('coupons', CouponController::class);
Route::get('transactions', [TransactionController::class, 'index']);
Route::get('products/{product}/reviews', [RatingsController::class, 'getReviewsOfProduct']);

//similar stores
Route::get('stores/{store}/similarStores', [StoreController::class, 'similarStores']);

Route::post('contact-us', [ContactController::class, 'store']);

Route::get('get-payment-methods', [PaymentMethodsController::class, 'index']);
Route::get('payment-methods/{payment_method}', [PaymentMethodsController::class, 'showPayment']);


Route::get('packages/{package}/reviews', [PackageController::class, 'getReviews']);

Route::resource('banners', BannerController::class);