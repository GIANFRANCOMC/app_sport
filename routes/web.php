<?php

use App\Http\Controllers\System\Auth\AuthenticatedSessionController;
use App\Http\Controllers\System\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(["web"])
     ->group(function() {

        Route::get('/send-subscription-emails',  [NotificationController::class, 'sendSubscriptionEmails'])->name('send-subscription-emails');

        $systemRoute = __DIR__.'/System';
        $guestRoute = __DIR__.'/Guest';

        // Guest Routes (Public)
        Route::prefix('{company_slug}/book_complaints')->middleware('company.exists')->group($guestRoute.'/BookComplaint.php');
        Route::prefix('{company_slug}/home')->middleware('company.exists')->group($guestRoute.'/Home.php');
        Route::prefix('{company_slug}/tracking_attendances')->middleware('company.exists')->group($guestRoute.'/TrackingAttendance.php');
        Route::prefix('{company_slug}/biometric_devices')->middleware('company.exists')->group($guestRoute.'/BiometricDevice.php');

        Route::middleware('guest')->group(function() use($guestRoute) {

            Route::get('/',  [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::get('login',  [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('login', [AuthenticatedSessionController::class, 'store']);

        });

        Route::middleware(['auth', 'verified'])->group(function() use($systemRoute) {

            // Assets
            Route::prefix('/assets')->group($systemRoute.'/Assets/Asset.php');
            Route::prefix('/assets_management')->group($systemRoute.'/Assets/AssetManagement.php');

            // Catalogs
            Route::prefix('/categories')->group($systemRoute.'/Catalogs/Category.php');
            Route::prefix('/products')->group($systemRoute.'/Catalogs/Product.php');
            Route::prefix('/services')->group($systemRoute.'/Catalogs/Service.php');
            Route::prefix('/subscriptions')->group($systemRoute.'/Catalogs/Subscription.php');

            // Customers
            Route::prefix('/customers')->group($systemRoute.'/Customers/Customer.php');
            Route::prefix('/tracking_attendances')->group($systemRoute.'/Customers/TrackingAttendance.php');
            Route::prefix('/tracking_customers')->group($systemRoute.'/Customers/TrackingCustomer.php');
            Route::prefix('/tracking_subscriptions')->group($systemRoute.'/Customers/TrackingSubscription.php');

            // Essentials
            Route::prefix('/dashboard')->group($systemRoute.'/Essentials/Dashboard.php');
            Route::prefix('/helpers')->group($systemRoute.'/Essentials/Helper.php');
            Route::prefix('/home')->group($systemRoute.'/Essentials/Home.php');
            Route::prefix('/reports')->group($systemRoute.'/Essentials/Report.php');

            // Customers (Tracking Notifications)
            Route::prefix('/tracking_notifications')->group($systemRoute.'/Customers/TrackingNotification.php');

            // Organizations
            Route::prefix('/book_complaints')->group($systemRoute.'/Organizations/BookComplaint.php');
            Route::prefix('/branches')->group($systemRoute.'/Organizations/Branch.php');
            Route::prefix('/companies')->group($systemRoute.'/Organizations/Company.php');
            Route::prefix('/users')->group($systemRoute.'/Organizations/User.php');

            // Sales
            Route::prefix('/sales')->group($systemRoute.'/Sales/Sale.php');

            // Warehouses
            Route::prefix('/stocks_management')->group($systemRoute.'/Warehouses/StockManagement.php');

            // Biometric Devices
            Route::prefix('/biometric_devices')->group($systemRoute.'/Biometric/BiometricDevice.php');

            // Sessions
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        });

  });
