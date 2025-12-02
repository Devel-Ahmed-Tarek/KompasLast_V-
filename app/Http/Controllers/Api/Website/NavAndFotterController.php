<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\FotterResource;
use App\Http\Resources\Website\NavbarResource;
use App\Models\NavFooter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class NavAndFotterController extends Controller
{
    public function nav(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        $data = NavFooter::first();
        return HelperFunc::sendResponse(200, '', new NavbarResource($data));
    }
    public function fotter(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        $data = NavFooter::first();
        return HelperFunc::sendResponse(200, '', new FotterResource($data));
    }
}
