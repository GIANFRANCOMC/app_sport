<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


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

use App\Http\Controllers\System\Auth\AuthenticatedSessionController;
use App\Http\Controllers\System\NotificationController;

Route::middleware(["web"])
     ->group(function() {

        Route::get('/send-subscription-emails',  [NotificationController::class, 'sendSubscriptionEmails'])->name('send-subscription-emails');

        $systemRoute = __DIR__.'/System';
        $guestRoute = __DIR__.'/Guest';

        Route::prefix('/reports')->group($systemRoute.'/Report.php');
        Route::prefix('{company_slug}/home')->middleware('company.exists')->group($guestRoute.'/Home.php');
        Route::prefix('{company_slug}/book_complaints')->middleware('company.exists')->group($guestRoute.'/BookComplaint.php');
        Route::prefix('{company_slug}/tracking_attendances')->middleware('company.exists')->group($guestRoute.'/TrackingAttendance.php');

        Route::middleware('guest')->group(function() use($guestRoute) {

            Route::get('/',  [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::get('login',  [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('login', [AuthenticatedSessionController::class, 'store']);

        });

        Route::middleware(['auth', 'verified'])->group(function() use($systemRoute) {

            Route::prefix('/assets_management')->group($systemRoute.'/Assets/AssetManagement.php');

            Route::prefix('/book_complaints')->group($systemRoute.'/BookComplaint.php');
            Route::prefix('/companies')->group($systemRoute.'/Company.php');
            Route::prefix('/products')->group($systemRoute.'/Product.php');
            Route::prefix('/services')->group($systemRoute.'/Service.php');
            Route::prefix('/tracking_customers')->group($systemRoute.'/TrackingCustomer.php');
            Route::prefix('/tracking_subscriptions')->group($systemRoute.'/TrackingSubscription.php');
            Route::prefix('/tracking_attendances')->group($systemRoute.'/TrackingAttendance.php');
            Route::prefix('/tracking_notifications')->group($systemRoute.'/TrackingNotification.php');
            Route::prefix('/subscriptions')->group($systemRoute.'/Subscription.php');
            Route::prefix('/customers')->group($systemRoute.'/Customer.php');
            Route::prefix('/stocks_management')->group($systemRoute.'/StockManagement.php');
            Route::prefix('/assets')->group($systemRoute.'/Asset.php');
            Route::prefix('/dashboard')->group($systemRoute.'/Dashboard.php');
            Route::prefix('/helpers')->group($systemRoute.'/Helper.php');
            Route::prefix('/home')->group($systemRoute.'/Home.php');
            Route::prefix('/branches')->group($systemRoute.'/Organizations/Branch.php');
            Route::prefix('/sales')->group($systemRoute.'/Sale.php');
            Route::prefix('/users')->group($systemRoute.'/User.php');
            Route::prefix('/categories')->group($systemRoute.'/Category.php');

            Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                 ->name('logout');

        });

  });
