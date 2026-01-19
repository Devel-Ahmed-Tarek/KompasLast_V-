<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CountryCityController extends Controller
{
    /**
     * Get all countries (for website form)
     * Returns countries with name in selected language only
     */
    public function getCountries(Request $request)
    {
        // Get language from query parameter or header, default to 'en'
        $language = $request->get('lang', $request->header('Accept-Language', 'en'));

        // Validate language
        $allowedLanguages = ['en', 'ar', 'de', 'fr', 'it'];
        if (!in_array($language, $allowedLanguages)) {
            $language = 'en';
        }

        App::setLocale($language);

        $countries = Country::all();

        // Transform countries to return only the name in selected language
        $countriesData = $countries->map(function ($country) use ($language) {
            // Get translation using Spatie Translatable, fallback to 'en' if translation not available
            $name = $country->getTranslation('name', $language, false);
            if (!$name) {
                $name = $country->getTranslation('name', 'en', false) ?? '';
            }

            return [
                'id' => $country->id,
                'name' => $name,
            ];
        });

        return HelperFunc::sendResponse(200, 'Countries retrieved successfully', $countriesData);
    }

    /**
     * Get all cities by country (for website form select)
     * Returns cities with name in selected language only
     */
    public function getCitiesByCountry(Request $request, $country_id)
    {
        // Get language from query parameter or header, default to 'en'
        $language = $request->get('lang', $request->header('Accept-Language', 'en'));

        // Validate language
        $allowedLanguages = ['en', 'ar', 'de', 'fr', 'it'];
        if (!in_array($language, $allowedLanguages)) {
            $language = 'en';
        }

        App::setLocale($language);

        // Validate country exists
        $country = Country::find($country_id);
        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        // Get all cities for this country
        $cities = City::where('country_id', $country_id)->get();

        // Transform cities to return only the name in selected language
        $citiesData = $cities->map(function ($city) use ($language) {
            // Get translation using Spatie Translatable, fallback to 'en' if translation not available
            $name = $city->getTranslation('name', $language, false);
            if (!$name) {
                $name = $city->getTranslation('name', 'en', false) ?? '';
            }

            return [
                'id' => $city->id,
                'name' => $name,
            ];
        });

        // Get country name in selected language
        $countryName = $country->getTranslation('name', $language, false);
        if (!$countryName) {
            $countryName = $country->getTranslation('name', 'en', false) ?? '';
        }

        return HelperFunc::sendResponse(200, 'Cities retrieved successfully', [
            'country' => [
                'id' => $country->id,
                'name' => $countryName,
            ],
            'cities' => $citiesData
        ]);
    }
}
