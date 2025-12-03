<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\AboutUsPageResource;
use App\Models\AboutUs;
use Illuminate\Support\Facades\App;

class AboutUsPageController extends Controller
{
    public function index()
    {
        $language = request()->get('lang', 'en');
        App::setLocale($language);
        $data = AboutUs::first()?->makeHidden(['created_at', 'updated_at']);
        return HelperFunc::sendResponse(200, 'done', new AboutUsPageResource($data));
    }
}
