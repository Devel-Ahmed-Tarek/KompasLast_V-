<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ServesPageResource;
use App\Http\Resources\Website\ServesSelectPageResource;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ServesPageController extends Controller
{
    public function index(Request $request, $slug)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        $serves = Type::with(['typeDitaliServices.media', 'TypeTips.media', 'TypeFeature', 'typeDitaliServesPageForm'])
            ->whereHas('typeDitaliServices', function ($query) use ($slug) {
                $query->whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$slug]);
            })
            ->firstOrFail();

        return HelperFunc::sendResponse(200, 'done', new ServesPageResource($serves));
    }

    public function select(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        // Check if flat list is requested
        $flat = $request->query('flat', false);

        if ($flat) {
            // Return flat list of all active types
            $serves = Type::where('is_active', true)->orderBy('parent_id')->orderBy('order')->get();
        } else {
            // Return hierarchical structure (parents with children)
            $serves = Type::whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)->orderBy('order');
                }])
                ->orderBy('order')
                ->get();
        }

        // Check if no data is found
        if ($serves->isEmpty()) {
            return HelperFunc::sendResponse(404, 'No data found for Type model', []);
        }

        // Return the data wrapped in a Resource
        return HelperFunc::sendResponse(200, 'done', ServesSelectPageResource::collection($serves));
    }

}
