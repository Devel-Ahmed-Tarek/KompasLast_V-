<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\PartnerPageResourcee;
use App\Models\PartnerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PartnerPageController extends Controller
{

    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $data = PartnerPage::first();
        return HelperFunc::sendResponse(200, 'done', new PartnerPageResourcee($data));

    }
}
