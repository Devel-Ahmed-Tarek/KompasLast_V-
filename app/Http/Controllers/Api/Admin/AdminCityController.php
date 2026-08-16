<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCityController extends Controller
{
    // عرض جميع المدن
    public function index(Request $request)
    {
        $query = City::with(['country', 'state']);

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }
        if ($request->filled('place_type')) {
            $query->where('place_type', $request->place_type);
        }

        $cities = $query->get();
        return HelperFunc::sendResponse(200, 'Cities retrieved successfully', $cities);
    }

    // عرض مدينة محددة
    public function show($id)
    {
        $city = City::with(['country', 'state'])->find($id);
        if (!$city) {
            return HelperFunc::sendResponse(404, 'City not found', []);
        }

        return HelperFunc::sendResponse(200, 'City retrieved successfully', $city);
    }

    // إنشاء مدينة جديدة
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'nullable|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'place_type' => 'nullable|in:city,municipality,region',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            $city = new City();
            $city->name = $request->name; // Save multilingual field as JSON
            $city->country_id = $request->country_id;
            $city->state_id = $request->state_id;
            $city->place_type = $request->place_type ?: City::TYPE_CITY;
            $city->latitude = $request->latitude;
            $city->longitude = $request->longitude;
            $city->save();

            return HelperFunc::sendResponse(201, 'City created successfully', $city->load(['country', 'state']));
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // تحديث مدينة موجودة
    public function update(Request $request, $id)
    {
        $city = City::find($id);
        if (!$city) {
            return HelperFunc::sendResponse(404, 'City not found', []);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|array',
            'name.en' => 'required_with:name|string',
            'name.ar' => 'nullable|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'country_id' => 'sometimes|required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'place_type' => 'nullable|in:city,municipality,region',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            if ($request->has('name')) {
                $city->name = $request->name;
            }
            if ($request->has('country_id')) {
                $city->country_id = $request->country_id;
            }
            if ($request->has('state_id')) {
                $city->state_id = $request->state_id;
            }
            if ($request->has('place_type')) {
                $city->place_type = $request->place_type;
            }
            if ($request->has('latitude')) {
                $city->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $city->longitude = $request->longitude;
            }
            $city->save();

            return HelperFunc::sendResponse(200, 'City updated successfully', $city->load(['country', 'state']));
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // حذف مدينة
    public function destroy($id)
    {
        $city = City::find($id);
        if (!$city) {
            return HelperFunc::sendResponse(404, 'City not found', []);
        }

        try {
            $city->delete();
            return HelperFunc::sendResponse(200, 'City deleted successfully', []);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // Get all cities by country
    public function getCitiesByCountry($country_id)
    {
        $validator = Validator::make(['country_id' => $country_id], [
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $country = Country::find($country_id);

        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        $cities = City::query()
            ->where('country_id', $country_id)
            ->selectable()
            ->with('state')
            ->get();

        return HelperFunc::sendResponse(200, 'Cities fetched successfully', [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
            ],
            'cities' => $cities
        ]);
    }

    public function getStates(Request $request)
    {
        return app(\App\Http\Controllers\Api\Website\CountryCityController::class)->getStates($request);
    }

    public function getPlacesByState(Request $request, $state_id)
    {
        return app(\App\Http\Controllers\Api\Website\CountryCityController::class)->getPlacesByState($request, $state_id);
    }
}
