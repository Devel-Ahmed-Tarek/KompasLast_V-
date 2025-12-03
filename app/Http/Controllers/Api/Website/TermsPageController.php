<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\TermsPageResource;
use App\Models\TremsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TermsPageController extends Controller
{
    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $page = TremsPage::first();
        return HelperFunc::sendResponse(200, '', new TermsPageResource($page));
    }

}
