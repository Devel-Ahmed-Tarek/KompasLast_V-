<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\PrivacyPageResource;
use App\Models\PrivacyPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PrivacyPageController extends Controller
{
    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $page = PrivacyPage::first();
        return HelperFunc::sendResponse(200, '', new PrivacyPageResource($page));
    }

}
