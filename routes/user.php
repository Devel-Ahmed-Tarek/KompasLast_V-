<?php

use App\Http\Controllers\Api\Admin\AdminComplaintController;
use App\Http\Controllers\Api\Website\AboutUsPageController;
use App\Http\Controllers\Api\Website\BlogPageController;
use App\Http\Controllers\Api\Website\CompaneisPageController;
use App\Http\Controllers\Api\Website\ContactPageController;
use App\Http\Controllers\Api\Website\FaqPageController;
use App\Http\Controllers\Api\Website\HomePageController;
use App\Http\Controllers\Api\Website\ImprintPageController;
use App\Http\Controllers\Api\Website\ModelOffersController;
use App\Http\Controllers\Api\Website\NavAndFotterController;
use App\Http\Controllers\Api\Website\OfferExecutionController;
use App\Http\Controllers\Api\Website\PartnerPageController;
use App\Http\Controllers\Api\Website\PrivacyPageController;
use App\Http\Controllers\Api\Website\ServesPageController;
use App\Http\Controllers\Api\Website\SitemapController;
use App\Http\Controllers\Api\Website\TermsPageController;
use App\Http\Controllers\Api\Website\VisitorController;
use App\Http\Controllers\Api\Website\CountryCityController;
use App\Http\Controllers\Api\User\OfferQuestionController;
use App\Http\Controllers\PageServesFormController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Route::prefix('user')->group(function () {

    Route::get('home', [HomePageController::class, 'index']);
    Route::get('aboutus', [AboutUsPageController::class, 'index']);
    Route::get('blogs', [BlogPageController::class, 'GetPageBlogs']);
    Route::get('blog/{slug}', [BlogPageController::class, 'index']);
    Route::get('blog/serves/{slug}', [BlogPageController::class, 'GetBlogByCategory']);

    Route::get('contact', [ContactPageController::class, 'index']);
    Route::get('companeis', [CompaneisPageController::class, 'index']);
    Route::get('serves/{slug}', [ServesPageController::class, 'index']);
    Route::get('serves/offer/{slug}', [PageServesFormController::class, 'index']);
    Route::get('faq', [FaqPageController::class, 'index']);
    Route::get('partner', [PartnerPageController::class, 'index']);
    Route::get('imprint', [ImprintPageController::class, 'index']);
    Route::get('privacy', [PrivacyPageController::class, 'index']);
    Route::get('terms', [TermsPageController::class, 'index']);

    // Countries and Cities for website forms
    Route::get('countries', [CountryCityController::class, 'getCountries']);
    Route::get('cities/by-country/{country_id}', [CountryCityController::class, 'getCitiesByCountry']);

    Route::get('company/{id}', [CompaneisPageController::class, 'getCompany']);
    Route::get('model/offers', [ModelOffersController::class, 'index']);

    Route::get('/offers/{id}/companies', [OfferExecutionController::class, 'getCompanysByOffer']);
    Route::post('/offers/executed/', [OfferExecutionController::class, 'storeExecution']);
    Route::post('/offers/rate', [OfferExecutionController::class, 'store']);
    Route::get('/offers/{id}/info', [OfferExecutionController::class, 'getInfoOffer']);
    Route::post('/offers/announcements', [OfferExecutionController::class, 'storeAnnouncement']);

    // Submit Offer Form (Complete Form Submission)
    Route::post('/offers/submit-form', [OfferQuestionController::class, 'submitOfferForm']);

    // Offer Questions Routes
    Route::prefix('offers/{offer_id}')->group(function () {
        Route::get('/questions/first', [OfferQuestionController::class, 'getFirstQuestion']);
        Route::get('/questions/{question_id}', [OfferQuestionController::class, 'getQuestion']);
        Route::post('/answer', [OfferQuestionController::class, 'submitAnswer']);
        Route::get('/answers', [OfferQuestionController::class, 'getAnswers']);
        Route::put('/answers/{answer_id}', [OfferQuestionController::class, 'updateAnswer']);
        Route::post('/answers/{answer_id}/files', [OfferQuestionController::class, 'uploadFiles']);
        Route::delete('/answers/{answer_id}/files/{file_id}', [OfferQuestionController::class, 'deleteFile']);
    });

    // Get Questions by Type ID
    Route::get('/types/{type_id}/questions', [App\Http\Controllers\Api\User\TypeQuestionController::class, 'getQuestions']);
});

Route::prefix('user')->middleware(['guest'])->group(function () {

    Route::get('type/select', [ServesPageController::class, 'select']);

    Route::get('nav', [NavAndFotterController::class, 'nav']);

    Route::get('fotter', [NavAndFotterController::class, 'fotter']);

    Route::post('add-offer', [HomePageController::class, 'store']);
    Route::post('add-review-site', [HomePageController::class, 'storeReivewSite']);

    Route::post('faq', [FaqPageController::class, 'sendFaq']);

    Route::post('contact', [ContactPageController::class, 'store']);
    Route::post('track/visitor', [VisitorController::class, 'trackVisitor']);

    Route::post('/complain', [AdminComplaintController::class, 'store']);

    Route::get('sitemap', [SitemapController::class, 'index']);
});

Route::get('/test-models', function () {
    $modelPath  = app_path('Models');
    $modelFiles = File::allFiles($modelPath);

    $results = [];

    foreach ($modelFiles as $file) {
        $className = 'App\\Models\\' . Str::replaceLast('.php', '', $file->getFilename());

        try {
            if (! class_exists($className)) {
                continue;
            }

            $model = new $className;

            // تحقق من وجود الجدول لتفادي crash لو الجدول مش موجود
            if (! Schema::hasTable($model->getTable())) {
                $results[$className] = '❌ Table not found: ' . $model->getTable();
                continue;
            }

            // محاولة عمل create
            $fillable = $model->getFillable();
            $fakeData = [];

            foreach ($fillable as $field) {
                $fakeData[$field] = 'test'; // كل القيم عبارة عن test
            }

            $model::create($fakeData);

            $results[$className] = '✅ OK';
        } catch (\Throwable $e) {
            $results[$className] = '❌ Error: ' . $e->getMessage();
            Log::error("Model {$className} failed: " . $e->getMessage());
        }
    }

    return response()->json($results);
});
