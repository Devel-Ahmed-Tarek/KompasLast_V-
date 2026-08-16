<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CountryCityController extends Controller
{
    private function resolveLanguage(Request $request): string
    {
        $language = $request->get('lang', $request->header('Accept-Language', 'en'));
        $language = substr((string) $language, 0, 2);
        $allowedLanguages = ['en', 'ar', 'de', 'fr', 'it'];

        return in_array($language, $allowedLanguages, true) ? $language : 'en';
    }

    private function translatedName($model, string $language): string
    {
        $name = $model->getTranslation('name', $language, false);
        if (! $name) {
            $name = $model->getTranslation('name', 'en', false) ?? '';
        }

        return $name;
    }

    public function getCountries(Request $request)
    {
        $language = $this->resolveLanguage($request);
        App::setLocale($language);

        $countries = Country::all()->map(function ($country) use ($language) {
            return [
                'id' => $country->id,
                'name' => $this->translatedName($country, $language),
            ];
        });

        return HelperFunc::sendResponse(200, 'Countries retrieved successfully', $countries);
    }

    public function getStates(Request $request)
    {
        $language = $this->resolveLanguage($request);
        App::setLocale($language);

        $countryId = $request->get('country_id');
        $query = State::query()->orderBy('id');
        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $states = $query->get()->map(function (State $state) use ($language) {
            return [
                'id'          => $state->id,
                'country_id'  => $state->country_id,
                'code'        => $state->code,
                'name'        => $this->translatedName($state, $language),
                'latitude'    => $state->latitude,
                'longitude'   => $state->longitude,
            ];
        });

        return HelperFunc::sendResponse(200, 'States retrieved successfully', $states);
    }

    public function getCitiesByCountry(Request $request, $country_id)
    {
        $language = $this->resolveLanguage($request);
        App::setLocale($language);

        $country = Country::find($country_id);
        if (! $country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        $query = City::query()
            ->where('country_id', $country_id)
            ->selectable()
            ->orderBy('id');

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('place_type') && in_array($request->place_type, [City::TYPE_CITY, City::TYPE_MUNICIPALITY], true)) {
            $query->where('place_type', $request->place_type);
        } elseif (! $request->filled('state_id')) {
            $query->where('place_type', City::TYPE_CITY);
        }

        $citiesData = $query->get()->map(function ($city) use ($language) {
            return [
                'id'         => $city->id,
                'state_id'   => $city->state_id,
                'place_type' => $city->place_type,
                'name'       => $this->translatedName($city, $language),
                'latitude'   => $city->latitude,
                'longitude'  => $city->longitude,
            ];
        });

        return HelperFunc::sendResponse(200, 'Cities retrieved successfully', [
            'country' => [
                'id' => $country->id,
                'name' => $this->translatedName($country, $language),
            ],
            'cities' => $citiesData,
        ]);
    }

    public function getPlacesByState(Request $request, $state_id)
    {
        $language = $this->resolveLanguage($request);
        App::setLocale($language);

        $state = State::find($state_id);
        if (! $state) {
            return HelperFunc::sendResponse(404, 'State not found', []);
        }

        $query = City::query()
            ->where('state_id', $state_id)
            ->selectable()
            ->orderBy('id');

        if ($request->filled('place_type') && in_array($request->place_type, [City::TYPE_CITY, City::TYPE_MUNICIPALITY], true)) {
            $query->where('place_type', $request->place_type);
        }

        $places = $query->get()->map(function ($city) use ($language) {
            return [
                'id'         => $city->id,
                'state_id'   => $city->state_id,
                'place_type' => $city->place_type,
                'name'       => $this->translatedName($city, $language),
                'latitude'   => $city->latitude,
                'longitude'  => $city->longitude,
            ];
        });

        return HelperFunc::sendResponse(200, 'Places retrieved successfully', [
            'state' => [
                'id'         => $state->id,
                'country_id' => $state->country_id,
                'code'       => $state->code,
                'name'       => $this->translatedName($state, $language),
            ],
            'places' => $places,
        ]);
    }
}
