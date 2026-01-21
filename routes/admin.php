<?php

use App\Http\Controllers\Api\Admin\AdminAboutUsPageController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminBlogController;
use App\Http\Controllers\Api\Admin\AdminCompanyController;
use App\Http\Controllers\Api\Admin\AdminComplaintController;
use App\Http\Controllers\Api\Admin\AdminContactController;
use App\Http\Controllers\Api\Admin\AdminContactPageController;
use App\Http\Controllers\Api\Admin\AdminCouponController;
use App\Http\Controllers\Api\Admin\AdminFaqController;
use App\Http\Controllers\Api\Admin\AdminFaqPageController;
use App\Http\Controllers\Api\Admin\AdminFormController;
use App\Http\Controllers\Api\Admin\AdminImprintPageController;
use App\Http\Controllers\Api\Admin\AdminModelOffersController;
use App\Http\Controllers\Api\Admin\AdminOfferController;
use App\Http\Controllers\Api\Admin\AdminOfferExecutionController;
use App\Http\Controllers\Api\Admin\AdminOfferFakeReportController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminOrderShopController;
use App\Http\Controllers\Api\Admin\AdminPageCompanyController;
use App\Http\Controllers\Api\Admin\AdminPageHomeController;
use App\Http\Controllers\Api\Admin\AdminPageNavFooterController;
use App\Http\Controllers\Api\Admin\AdminPartnerPageController;
use App\Http\Controllers\Api\Admin\AdminPrivacyPageController;
use App\Http\Controllers\Api\Admin\AdminReviewcompanyController;
use App\Http\Controllers\Api\Admin\AdminReviewsCompanyReportController;
use App\Http\Controllers\Api\Admin\AdminReviewSiteController;
use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\AdminTremsPageController;
use App\Http\Controllers\Api\Admin\AdminTypeController;
use App\Http\Controllers\Api\Admin\AdminTypeDitaliServicesController;
use App\Http\Controllers\Api\Admin\AdminTypeFeatureController;
use App\Http\Controllers\Api\Admin\AdminTypeTipsController;
use App\Http\Controllers\Api\Admin\AdminTypeQuestionController;
use App\Http\Controllers\Api\Admin\AdminQuestionOptionController;
use App\Http\Controllers\Api\Admin\AdminVisitorController;
use App\Http\Controllers\Api\Admin\ConfigAppController;
use App\Http\Controllers\Api\Admin\AdminCountryController;
use App\Http\Controllers\Api\Admin\AdminCityController;
use App\Http\Controllers\Api\Admin\ServesPageFormController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\BlogsPageController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationsController;
use Illuminate\Support\Facades\Route;

// Admin Authentication
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::prefix('admin')->middleware(['auth:sanctum', 'typeUser:admin'])->group(function () {

    // Admin dashboard
    Route::prefix('dashboard/')->group(function () {
        Route::get('get-daily-statistics', [DashboardController::class, 'getDailyStatistics']);
        Route::get('get-daily-offer', [DashboardController::class, 'getOffersChartData']);
        Route::get('get-info-data', [DashboardController::class, 'getInfoDataToDashboard']);
    });
    // https://kompass.alwekala.store/public/api/admin/teps/types/19
    // Authentication routes
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('change-password', [AdminAuthController::class, 'changePassword']);

    // Admin profile management
    Route::post('profile-update/{id}', [UserController::class, 'update']);
    Route::post('company/profile-update/{idCompany}', [UserController::class, 'updateCompany']);
    Route::get('profile/auth', [UserController::class, 'profile_auth']);
    Route::get('profile/{id}', [UserController::class, 'profile']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
    //
    Route::get('/offer-execut', [AdminOfferExecutionController::class, 'index']);
    Route::get('/offer-cnnouncement', [AdminOfferExecutionController::class, 'AnnouncementGet']);
    // Types management (with hierarchy support)
    Route::get('/types', [AdminTypeController::class, 'index']);
    Route::get('/types/parents', [AdminTypeController::class, 'getParentTypes']);
    Route::get('/types/{id}', [AdminTypeController::class, 'show']);
    Route::get('/types/{id}/children', [AdminTypeController::class, 'getChildren']);
    Route::post('/types', [AdminTypeController::class, 'store']);
    Route::put('/types/{id}', [AdminTypeController::class, 'update']);
    Route::delete('/types/{id}', [AdminTypeController::class, 'destroy']);
    Route::post('/types/reorder', [AdminTypeController::class, 'reorder']);
    Route::put('/types/{id}/toggle-active', [AdminTypeController::class, 'toggleActive']);

    // Orders management
    Route::get('orders/pending', [AdminOrderController::class, 'pending']);
    Route::get('orders/all', [AdminOrderController::class, 'all']);
    Route::get('orders/confirmed', [AdminOrderController::class, 'confirmed']);
    Route::get('orders/canceled', [AdminOrderController::class, 'canceled']);
    Route::post('orders/{order}/status/{status}', [AdminOrderController::class, 'ChangeStatus']);
    // Offers management
    Route::post('offers/update/{id}', [AdminOfferController::class, 'update']);
    Route::patch('offers/update/{id}/status/{status}', [AdminOfferController::class, 'updateStatus']);
    Route::post('offers', [AdminOfferController::class, 'store']);
    Route::get('offers', [AdminOfferController::class, 'getFilteredOffers']);
    Route::get('offers/{id}', [AdminOfferController::class, 'show']);

    // Contacts management
    Route::get('contact', [AdminContactController::class, 'index']);
    Route::post('contact', [AdminContactController::class, 'store']);

    // Company user management
    Route::get('user/get/pending/companies', [UserController::class, 'getPendingCompanies']);
    Route::get('user/get/sucsess/companies', [UserController::class, 'getsucsessCompanies']);
    Route::post('user/ban/company/{company_id}/{banValue}', [UserController::class, 'banCompany']);
    Route::post('user/status/company/{company_id}/{statusValue}', [UserController::class, 'statusCompany']);
    Route::post('user/accept/company/{company_id}/', [UserController::class, 'acceptCompany']);
    Route::get('user/company/{company_id}', [UserController::class, 'getCompany']);
    // create Admin user
    Route::post('create', [AdminAuthController::class, 'registerPost']);
    Route::delete('user/delete/{id}', [UserController::class, 'destroy']);

    // General user management
    Route::get('user', [UserController::class, 'getUserAdmin']);

    // Visitor data
    Route::get('/visitors', [AdminVisitorController::class, 'index']);

    // Complaints management
    Route::get('/complaints', [AdminComplaintController::class, 'index']);
    Route::post('/complaints', [AdminComplaintController::class, 'store']);
    Route::put('/complaints/{id}/status', [AdminComplaintController::class, 'updateStatus']);

    // Reviews management comapny
    Route::get('reviews', [AdminReviewcompanyController::class, 'index']);
    Route::post('reviews', [AdminReviewcompanyController::class, 'store']);
    Route::get('reviews/overview', [AdminReviewcompanyController::class, 'overview']);
    Route::get('reviews/{id}', [AdminReviewcompanyController::class, 'show']);
    Route::put('reviews/{id}', [AdminReviewcompanyController::class, 'update']);
    Route::put('reviews/{id}/status', [AdminReviewcompanyController::class, 'updateStatus']);
    Route::delete('reviews/{id}', [AdminReviewcompanyController::class, 'destroy']);

    // Reviews management Kompas
    Route::prefix('reviews-site')->group(function () {
        Route::get('/', [AdminReviewSiteController::class, 'index']);
        Route::post('/', [AdminReviewSiteController::class, 'store']);
        Route::get('/overview', [AdminReviewSiteController::class, 'overview']);
        Route::put('/{id}', [AdminReviewSiteController::class, 'update']);
        Route::delete('/{id}', [AdminReviewSiteController::class, 'destroy']);
        Route::put('/{id}/status', [AdminReviewSiteController::class, 'updateStatus']);
    });

    Route::post('/send-email-to-company', [EmailController::class, 'sendEmailToCompany']);

    // Orders Shop
    Route::get('/order-list', [AdminOrderShopController::class, 'index']);
    Route::post('/order-list', [AdminOrderShopController::class, 'store']);

    Route::get('/config', [ConfigAppController::class, 'index']);
    Route::post('/config', [ConfigAppController::class, 'update']);

    Route::apiResource('reviews-company-reports', AdminReviewsCompanyReportController::class);

    Route::apiResource('offer-fake-reports', AdminOfferFakeReportController::class);

    Route::apiResource('details/types', AdminTypeDitaliServicesController::class);
    Route::post('/update/details/types/{id}', [AdminTypeDitaliServicesController::class, 'update']);
    Route::apiResource('form/offer/types', ServesPageFormController::class);
    Route::post('/update/details/offer/types/{id}', [ServesPageFormController::class, 'update']);

    Route::apiResource('teps/types', AdminTypeTipsController::class);

    Route::apiResource('feature/types', AdminTypeFeatureController::class);

    // Admin Questions Management
    Route::prefix('types/{type_id}/questions')->group(function () {
        Route::get('/', [AdminTypeQuestionController::class, 'index']);
        Route::post('/', [AdminTypeQuestionController::class, 'store']);
        Route::get('/flow-tree', [AdminTypeQuestionController::class, 'getFlowTree']);
        Route::get('/{id}', [AdminTypeQuestionController::class, 'show']);
        Route::put('/{id}', [AdminTypeQuestionController::class, 'update']);
        Route::delete('/{id}', [AdminTypeQuestionController::class, 'destroy']);
        Route::put('/{id}/reorder', [AdminTypeQuestionController::class, 'reorder']);
    });

    // Admin Question Options Management
    Route::prefix('questions/{question_id}/options')->group(function () {
        Route::get('/', [AdminQuestionOptionController::class, 'index']);
        Route::post('/', [AdminQuestionOptionController::class, 'store']);
        Route::get('/{id}', [AdminQuestionOptionController::class, 'show']);
        Route::put('/{id}', [AdminQuestionOptionController::class, 'update']);
        Route::delete('/{id}', [AdminQuestionOptionController::class, 'destroy']);
    });

    Route::prefix('general')->group(function () {

        Route::get('nav_footers', [AdminPageNavFooterController::class, 'index']);
        Route::post('nav_footers', [AdminPageNavFooterController::class, 'update']);

        // Admin Home
        Route::get('home', [AdminPageHomeController::class, 'index']);
        Route::post('home', [AdminPageHomeController::class, 'update']);

        // Admin From
        Route::get('form', [AdminFormController::class, 'index']);
        Route::post('form', [AdminFormController::class, 'update']);

        // Admin About
        Route::get('about', [AdminAboutUsPageController::class, 'index']);
        Route::post('about', [AdminAboutUsPageController::class, 'update']);

        // Admin FAQ
        Route::get('pag-faq', [AdminFaqPageController::class, 'index']);
        Route::post('faq/{id}', [AdminFaqPageController::class, 'update']);

        // Admin Companies
        Route::get('pag-companies', [AdminPageCompanyController::class, 'index']);
        Route::post('pag-companies/{id}', [AdminPageCompanyController::class, 'update']);

        // Admin Contact
        Route::get('pag-contact', [AdminContactPageController::class, 'index']);
        Route::post('pag-contact/{id}', [AdminContactPageController::class, 'update']);

        // Admin Imprint
        Route::get('page-imprint', [AdminImprintPageController::class, 'index']);
        Route::post('page-imprint', [AdminImprintPageController::class, 'update']);

        // Admin Partnaer
        Route::prefix('partner-page')->group(function () {
            Route::get('/', [AdminPartnerPageController::class, 'index']);
            Route::post('/', [AdminPartnerPageController::class, 'update']);
        });

        //BLOG
        Route::get('blog', [AdminBlogController::class, 'index']);
        Route::get('blog/{id}', [AdminBlogController::class, 'show']);
        Route::delete('blog/{id}', [AdminBlogController::class, 'deleted']);
        Route::post('blog', [AdminBlogController::class, 'store']);
        Route::post('blog/update/{id}', [AdminBlogController::class, 'update']);
        Route::put('blog/status/{id}/{status}', [AdminBlogController::class, 'updateStatus']);

        //BLOG Page
        Route::get('blog-page', [BlogsPageController::class, 'index']);
        Route::post('blog-page/update/{id}', [BlogsPageController::class, 'update']);

        // Admin FAQ
        Route::apiResource('faq', AdminFaqController::class);

        // Admin TremsPage
        Route::get('terms', [AdminTremsPageController::class, 'index']);
        Route::post('terms', [AdminTremsPageController::class, 'update']);

        // Admin PrivacyPage
        Route::get('privacy', [AdminPrivacyPageController::class, 'index']);
        Route::post('privacy', [AdminPrivacyPageController::class, 'update']);

        // Admin model-offers
        Route::put('model/offers/{id}/status', [AdminModelOffersController::class, 'updateStatus']);
        Route::apiResource('model/offers', AdminModelOffersController::class);
    });

    // Admin Coupons
    Route::get('coupons', [AdminCouponController::class, 'index']);
    Route::post('coupons', [AdminCouponController::class, 'store']);
    Route::get('coupons/{id}', [AdminCouponController::class, 'show']);
    Route::post('coupons/{id}', [AdminCouponController::class, 'update']);
    Route::delete('coupons/{id}', [AdminCouponController::class, 'destroy']);

    // Role admin Api Controller
    Route::prefix('roles')->group(function () {
        Route::get('get/single/{id}', [AdminRoleController::class, 'getSingeleRole']);
        Route::get('permissions/', [AdminRoleController::class, 'permissions']);
        Route::post('permissions/create', [AdminRoleController::class, 'permissions_create']);
    });

    //AdminRoleController
    Route::apiResource('roles', AdminRoleController::class);

    // Admin Notification
    Route::prefix('notifications')->middleware('check.notification')->group(function () {
        Route::get('/{limt}/{filter}', [NotificationsController::class, 'index']);
        Route::get('/unreading', [NotificationsController::class, 'getAllUnreadNotifications']);
        Route::get('/reading', [NotificationsController::class, 'getAllReadNotifications']);
        Route::put('/read/{id}', [NotificationsController::class, 'update']);
        Route::delete('/delete/{id}', [NotificationsController::class, 'destroy']);
    });

    // Admin company management
    Route::post('update-file/{companyId}', [AdminCompanyController::class, 'updateFile']);
    Route::delete('deleted-company/{companyId}', [AdminCompanyController::class, 'deletedCompany']);
    Route::post('update-expired-date/{companyId}', [AdminCompanyController::class, 'updateExpiredDate']);
    Route::post('add/types', [AdminCompanyController::class, 'addType']);
    Route::post('remove/types', [AdminCompanyController::class, 'deleteType']);
    Route::get('company/types/dont/haveing', [AdminCompanyController::class, 'gityourType']);
    Route::get('company/types/haveing', [AdminCompanyController::class, 'TypesHaveing']);

    // Company Countries management
    Route::get('company/countries/available', [AdminCompanyController::class, 'getAvailableCountries']);
    Route::post('company/countries/add', [AdminCompanyController::class, 'addCountry']);
    Route::post('company/countries/remove', [AdminCompanyController::class, 'deleteCountry']);
    Route::get('company/countries/all', [AdminCompanyController::class, 'getSubscribedCountries']);

    // Company Cities management
    Route::get('company/cities/available', [AdminCompanyController::class, 'getAvailableCities']);
    Route::post('company/cities/add', [AdminCompanyController::class, 'addCity']);
    Route::post('company/cities/remove', [AdminCompanyController::class, 'deleteCity']);
    Route::get('company/cities/all', [AdminCompanyController::class, 'getSubscribedCities']);

    // Countries management
    Route::get('countries', [AdminCountryController::class, 'index']);
    Route::post('countries', [AdminCountryController::class, 'store']);
    Route::get('countries/{id}', [AdminCountryController::class, 'show']);
    Route::put('countries/{id}', [AdminCountryController::class, 'update']);
    Route::delete('countries/{id}', [AdminCountryController::class, 'destroy']);

    // Cities management
    Route::get('cities', [AdminCityController::class, 'index']);
    Route::post('cities', [AdminCityController::class, 'store']);
    Route::get('cities/{id}', [AdminCityController::class, 'show']);
    Route::put('cities/{id}', [AdminCityController::class, 'update']);
    Route::delete('cities/{id}', [AdminCityController::class, 'destroy']);
    Route::get('cities/by-country/{country_id}', [AdminCityController::class, 'getCitiesByCountry']);
});
