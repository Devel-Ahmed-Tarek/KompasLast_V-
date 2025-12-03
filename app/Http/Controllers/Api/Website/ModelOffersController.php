<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ModelOffersResource;
use App\Models\ModelOffers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ModelOffersController extends Controller
{

    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        // Fetch paginated list of offers
        $offers = ModelOffers::paginate(1);

        // Append full image URL to each offer
        $offers->getCollection()->transform(function ($offer) {
            $offer->img = $offer->img ? asset($offer->img) : null;
            return $offer;
        });

        // Return paginated response using HelperFunc
        return HelperFunc::pagination($offers, ModelOffersResource::collection($offers));
    }
}
