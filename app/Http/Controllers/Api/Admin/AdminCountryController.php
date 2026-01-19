<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCountryController extends Controller
{
    // عرض جميع الدول
    public function index()
    {
        $countries = Country::with('cities')->get();
        return HelperFunc::sendResponse(200, 'Countries retrieved successfully', $countries);
    }

    // عرض دولة محددة
    public function show($id)
    {
        $country = Country::with('cities')->find($id);
        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        return HelperFunc::sendResponse(200, 'Country retrieved successfully', $country);
    }

    // إنشاء دولة جديدة
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
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            $country = new Country();
            $country->name = $request->name; // Save multilingual field as JSON
            $country->save();

            return HelperFunc::sendResponse(201, 'Country created successfully', $country);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // تحديث دولة موجودة
    public function update(Request $request, $id)
    {
        $country = Country::find($id);
        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|array',
            'name.en' => 'required_with:name|string',
            'name.ar' => 'nullable|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            if ($request->has('name')) {
                $country->name = $request->name;
            }
            $country->save();

            return HelperFunc::sendResponse(200, 'Country updated successfully', $country);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // حذف دولة
    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        try {
            $country->delete();
            return HelperFunc::sendResponse(200, 'Country deleted successfully', []);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }
}
