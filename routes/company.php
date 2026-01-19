<?php

use App\Http\Controllers\Api\Admin\ConfigAppController;
use App\Http\Controllers\Api\Company\CompanyAuthController;
use App\Http\Controllers\Api\Company\CompanyDachbouredController;
use App\Http\Controllers\Api\Company\CompanyOrderController;
use App\Http\Controllers\Api\Company\CompanyProfileController;
use App\Http\Controllers\Api\Company\OfferController;
use App\Http\Controllers\Api\Company\OfferFakeReportController;
use App\Http\Controllers\Api\Company\PaymentController;
use App\Http\Controllers\Api\Company\ReviewcompanyController;
use App\Http\Controllers\Api\Company\ReviewsCompanyReportController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('company/login', [CompanyAuthController::class, 'loginPost']);
Route::post('company/register', [CompanyAuthController::class, 'registerPost']);
Route::post('send-otp', [CompanyAuthController::class, 'sendOtp']);
Route::post('check-otp', [CompanyAuthController::class, 'checkOtp']);
Route::post('change-password', [CompanyAuthController::class, 'changePassword']);
Route::get('company/contract/pdf', [CompanyProfileController::class, 'generateContractPdf']);
Route::get('company/handle/webhook', [PaymentController::class, 'handleWebhook']);

Route::prefix('company')->middleware(['auth:sanctum', 'typeUser:company'])->group(function () {

    Route::post('logout', [CompanyAuthController::class, 'logout']);

    Route::get('git/types/dont/haveing', [CompanyProfileController::class, 'gityourType']);
    Route::post('add/types', [CompanyProfileController::class, 'addType']);
    Route::post('remove/types', [CompanyProfileController::class, 'deleteType']);
    Route::get('all/types', [CompanyProfileController::class, 'getSubscribedTypes']);

    // Countries management
    Route::get('countries/available', [CompanyProfileController::class, 'getAvailableCountries']);
    Route::post('countries/add', [CompanyProfileController::class, 'addCountry']);
    Route::post('countries/remove', [CompanyProfileController::class, 'deleteCountry']);
    Route::get('countries/all', [CompanyProfileController::class, 'getSubscribedCountries']);

    // Cities management
    Route::get('cities/available', [CompanyProfileController::class, 'getAvailableCities']);
    Route::post('cities/add', [CompanyProfileController::class, 'addCity']);
    Route::post('cities/remove', [CompanyProfileController::class, 'deleteCity']);
    Route::get('cities/all', [CompanyProfileController::class, 'getSubscribedCities']);
    Route::get('profile/auth', [CompanyProfileController::class, 'profile_auth']);

    Route::get('dashboard', [CompanyDachbouredController::class, 'index']);
    Route::get('dashboard/calendar', [CompanyDachbouredController::class, 'calendar']);

    Route::post('change-password', [CompanyAuthController::class, 'changePassword']);

    Route::post('orders/stripe', [PaymentController::class, 'createPaymentLink']);
    Route::post('orders/stripe/hoke', [PaymentController::class, 'hoke']);
    Route::get('orders', [CompanyOrderController::class, 'index'])->name('orders.index');
    Route::post('orders', [CompanyOrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}/edit', [CompanyOrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{order}', [CompanyOrderController::class, 'update'])->name('orders.update');
    Route::get('orders/taps', [CompanyOrderController::class, 'tap'])->name('orders.tap');
    Route::delete('orders/{order}', [CompanyOrderController::class, 'destroy'])->name('orders.destroy');

    Route::get('/shop', [OfferController::class, 'index']);
    Route::post('by-Offer', [OfferController::class, 'byOffer']);
    Route::get('coupons', [OfferController::class, 'getCouponByCode']);
    Route::get('get-my-offer', [OfferController::class, 'getMyOffer']);
    Route::get('get-my-completed-offer', [OfferController::class, 'getCompletedOffer']);
    Route::get('singel-offer/{id}', [OfferController::class, 'singelOffer']);

    Route::get('/{id}/profile', [CompanyProfileController::class, 'show']);
    Route::put('/{status}/profile', [CompanyProfileController::class, 'updateDinamicOfferCompany']);
    Route::post('/{id}/profile', [CompanyProfileController::class, 'update']);

    Route::get('review', [ReviewcompanyController::class, 'index']);
    Route::get('review/overview', [ReviewcompanyController::class, 'overview']);

    Route::get('/config', [ConfigAppController::class, 'index']);

    Route::apiResource('reviews-company-reports', ReviewsCompanyReportController::class);

    Route::apiResource('offer-fake-reports', OfferFakeReportController::class);

    Route::post('/send-email-to-kompas', [EmailController::class, 'sendEmailToCompany']);

    // company Notification
    Route::get('notifications/{limt}/{filter}', [NotificationsController::class, 'index']);
    Route::put('notifications/read/all', [NotificationsController::class, 'markAllAsRead']);
    Route::get('notifications/unreading', [NotificationsController::class, 'getAllUnreadNotifications']);
    Route::get('notifications/reading', [NotificationsController::class, 'getAllReadNotifications']);
    Route::put('notification/read/{id}', [NotificationsController::class, 'update']);
    Route::delete('notification/delete/{id}', [NotificationsController::class, 'destroy']);
});
