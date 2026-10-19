<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\{AddressController, AdminController, AuthController, CatalogController, CheckoutController, DineInController, OrderController, ReviewController, RiderController, StaffController, PaymentController, NotificationController, ReportController, PosController, DigitalTwinController, IntegrationController, LoyaltyWalletController, PromotionController, AnalyticsController};

Route::prefix('v1')->middleware(['throttle:60,1','audit'])->group(function () {
    Route::get('/health', function () {
        try {
            DB::select('select 1');
            return [
                'status' => 'ok',
                'service' => 'FoodHub API',
                'version' => env('FOODHUB_VERSION', '1.5.0'),
                'database' => 'ok',
                'time' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'degraded',
                'service' => 'FoodHub API',
                'version' => env('FOODHUB_VERSION', '1.5.0'),
                'database' => 'unavailable',
            ], 503);
        }
    });

    Route::get('/health/detailed', function () {
        $started = microtime(true);
        $checks = [];
        try { DB::select('select 1'); $checks['database'] = 'ok'; } catch (\Throwable $e) { $checks['database'] = 'failed'; }
        try { app('cache')->put('foodhub_healthcheck', 'ok', 5); $checks['cache'] = app('cache')->get('foodhub_healthcheck') === 'ok' ? 'ok' : 'failed'; } catch (\Throwable $e) { $checks['cache'] = 'failed'; }
        $checks['app_key'] = (bool) config('app.key') ? 'ok' : 'missing';
        $healthy = !in_array('failed', $checks, true) && $checks['app_key'] === 'ok';
        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'service' => 'FoodHub API',
            'version' => env('FOODHUB_VERSION', '1.5.0'),
            'checks' => $checks,
            'latency_ms' => round((microtime(true) - $started) * 1000, 2),
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    })->middleware('throttle:30,1');

    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/branches', [CatalogController::class, 'branches']);
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/products', [CatalogController::class, 'products']);
    Route::get('/products/{product}', [CatalogController::class, 'show']);
    Route::post('/checkout/quote', [CheckoutController::class, 'quote']);
    Route::get('/dine-in/tables/{token}', [DineInController::class, 'table']);
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
    Route::get('/integrations/status', [IntegrationController::class, 'status']);
    Route::get('/promotions', [PromotionController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/wallet', [LoyaltyWalletController::class, 'show']);
        Route::get('/wallet/transactions', [LoyaltyWalletController::class, 'transactions']);
        Route::get('/loyalty/transactions', [LoyaltyWalletController::class, 'loyaltyTransactions']);
        Route::post('/loyalty/redeem', [LoyaltyWalletController::class, 'redeem']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::apiResource('addresses', AddressController::class)->except(['show', 'create', 'edit']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/tracking', [OrderController::class, 'tracking']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::get('/payments/methods', [PaymentController::class, 'methods']);
        Route::post('/orders/{order}/payment', [PaymentController::class, 'initiate']);
        Route::post('/orders/{order}/payment/paypal/capture', [PaymentController::class, 'paypalCapture']);
        Route::post('/orders/{order}/refund', [PaymentController::class, 'refund'])->middleware('role:admin');
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/devices', [IntegrationController::class, 'registerDevice']);
        Route::post('/maps/geocode', [IntegrationController::class, 'geocode']);
        Route::post('/maps/directions', [IntegrationController::class, 'directions']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
        Route::post('/dine-in/orders', [DineInController::class, 'order']);

        Route::prefix('staff')->middleware('staff')->group(function () {
            Route::get('/dashboard', [StaffController::class, 'dashboard']);
            Route::get('/orders', [StaffController::class, 'orders']);
            Route::post('/orders/{order}/transition', [StaffController::class, 'transition']);
            Route::post('/orders/{order}/assign-rider', [StaffController::class, 'assignRider']);
        });

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
            Route::get('/orders', [AdminController::class, 'orders']);
            Route::get('/orders/{order}', [AdminController::class, 'order']);
            Route::post('/orders/{order}/transition', [AdminController::class, 'transitionOrder']);

            Route::get('/branches', [AdminController::class, 'branches']);
            Route::post('/branches', [AdminController::class, 'storeBranch']);
            Route::put('/branches/{branch}', [AdminController::class, 'updateBranch']);
            Route::delete('/branches/{branch}', [AdminController::class, 'deleteBranch']);
            Route::put('/branches/{branch}/products/{product}', [AdminController::class, 'updateBranchProduct']);

            Route::get('/categories', [AdminController::class, 'categories']);
            Route::post('/categories', [AdminController::class, 'storeCategory']);
            Route::put('/categories/{category}', [AdminController::class, 'updateCategory']);
            Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory']);

            Route::get('/products', [AdminController::class, 'products']);
            Route::post('/products', [AdminController::class, 'storeProduct']);
            Route::put('/products/{product}', [AdminController::class, 'updateProduct']);
            Route::delete('/products/{product}', [AdminController::class, 'deleteProduct']);
            Route::get('/products/{product}/options', [AdminController::class, 'productOptions']);
            Route::post('/products/{product}/options', [AdminController::class, 'storeProductOption']);
            Route::delete('/product-options/{option}', [AdminController::class, 'deleteProductOption']);

            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{user}', [AdminController::class, 'updateUser']);
            Route::get('/inventory', [AdminController::class, 'inventory']);
            Route::post('/inventory', [AdminController::class, 'storeInventory']);
            Route::post('/inventory/{item}/adjust', [AdminController::class, 'adjustInventory']);
            Route::get('/tables', [AdminController::class, 'tables']);
            Route::put('/tables/{table}', [AdminController::class, 'updateTable']);
            Route::get('/coupons', [AdminController::class, 'coupons']);
            Route::post('/coupons', [AdminController::class, 'storeCoupon']);
            Route::put('/coupons/{coupon}', [AdminController::class, 'updateCoupon']);
            Route::delete('/coupons/{coupon}', [AdminController::class, 'deleteCoupon']);
            Route::get('/promotions', [PromotionController::class, 'adminIndex']);
            Route::post('/promotions', [PromotionController::class, 'store']);
            Route::put('/promotions/{promotion}', [PromotionController::class, 'update']);
            Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy']);
            Route::post('/users/{user}/wallet/credit', [LoyaltyWalletController::class, 'adminAdjust']);
            Route::post('/users/{user}/loyalty/adjust', [LoyaltyWalletController::class, 'adminLoyaltyAdjust']);
        });

        Route::prefix('reports')->middleware('role:admin,branch_manager')->group(function () {
            Route::get('/summary', [ReportController::class, 'summary']);
            Route::get('/sales', [ReportController::class, 'sales']);
            Route::get('/top-products', [ReportController::class, 'topProducts']);
            Route::get('/cancellation', [ReportController::class, 'cancellation']);
            Route::get('/payment-mix', [ReportController::class, 'paymentMix']);
            Route::get('/audit', [ReportController::class, 'audit'])->middleware('role:admin');
            Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
            Route::get('/analytics/hourly-demand', [AnalyticsController::class, 'hourlyDemand']);
            Route::get('/analytics/branch-performance', [AnalyticsController::class, 'branchPerformance']);
            Route::get('/analytics/retention', [AnalyticsController::class, 'retention']);
        });

        Route::prefix('pos')->middleware('role:admin,branch_manager,waiter')->group(function () {
            Route::post('/orders', [PosController::class, 'store']);
        });

        Route::prefix('digital-twin')->middleware('role:admin,branch_manager')->group(function () {
            Route::get('/snapshot', [DigitalTwinController::class, 'snapshot']);
            Route::post('/simulate', [DigitalTwinController::class, 'simulate']);
            Route::post('/compare', [DigitalTwinController::class, 'compare']);
        });

        Route::prefix('rider')->middleware('rider')->group(function () {
            Route::get('/queue', [RiderController::class, 'queue']);
            Route::post('/availability', [RiderController::class, 'availability']);
            Route::get('/assignments/{assignment}/locations', [RiderController::class, 'locations']);
            Route::post('/assignments/{assignment}/accept', [RiderController::class, 'accept']);
            Route::post('/assignments/{assignment}/pickup', [RiderController::class, 'pickup']);
            Route::post('/assignments/{assignment}/out-for-delivery', [RiderController::class, 'outForDelivery']);
            Route::post('/assignments/{assignment}/location', [RiderController::class, 'location']);
            Route::post('/assignments/{assignment}/deliver', [RiderController::class, 'deliver']);
        });
    });
});
