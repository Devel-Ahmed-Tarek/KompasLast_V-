<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Website\HomePageController;

Route::get('/', function () {
    return view('welcome');
});

// Confirm offer route for email link (web, not api)
Route::get('/user/offers/confirm/{token}', [HomePageController::class, 'confirmOffer']);
