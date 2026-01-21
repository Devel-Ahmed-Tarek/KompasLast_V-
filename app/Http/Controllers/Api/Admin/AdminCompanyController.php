<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyDetail;
use App\Models\Type;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCompanyController extends Controller
{
    public function updateCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string',
            'phone'        => 'nullable|string',
            'phone2'       => 'nullable|string',
            'email'        => 'nullable|email|unique:users,email,' . $id,
            'img'          => 'nullable|file|mimes:jpg,jpeg,png',
            'address'      => 'nullable|string',
            'website'      => 'nullable|url',
            'about'        => 'nullable|string',
            'founded_year' => 'nullable|string',
            'banc_count'   => 'nullable|string',
            'banc_ip'      => 'nullable|string',
            'banc_name'    => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,doc,docx',
            'file2'        => 'nullable|file|mimes:pdf,doc,docx',
            'file3'        => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 422);
        }

        $company = User::find($id);

        if (! $company || $company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }

        // تحديث بيانات المستخدم
        $fieldsToUpdate = ['name', 'phone', 'email'];
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) {
                $company->$field = $request->$field;
            }
        }

        if ($request->hasFile('img')) {
            $company->img = HelperFunc::uploadFile('images', $request->file('img'));
        }

        $company->save();

        // تحديث أو إنشاء تفاصيل الشركة
        $companyDetails = $company->companyDetails ?? new CompanyDetail(['user_id' => $company->id]);

        $detailsFields = ['address', 'website', 'about', 'number', 'phone2', 'description', 'banc_ip', 'banc_count', 'banc_name', 'founded_year'];
        foreach ($detailsFields as $field) {
            if ($request->has($field)) {
                $companyDetails->$field = $request->$field;
            }
        }

        // تحديث الملفات
        $files = ['file', 'file2', 'file3'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $companyDetails->$file = HelperFunc::uploadFile('files', $request->file($file));
                $companyDetails->count_offer++;
            }
        }

        // تحقق من الحالة بناءً على عدد العروض
        if ($companyDetails->count_offer > 3) {
            $companyDetails->status = 1;
        }

        $companyDetails->save();
        return HelperFunc::sendResponse(200, __('messages.updated_successfully'));
    }

    public function deletedCompany(Request $request, $id)
    {
        $company = User::find($id);

        if (! $company || $company->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found');
        }

        if ($company->companyDetails) {
            $company->companyDetails->delete();
        }

        $company->delete();

        return HelperFunc::sendResponse(200, __('messages.deleted_successfully'));
    }

    public function updateFile($companyId, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file'  => 'nullable|file|mimes:pdf,doc,docx',
            'file2' => 'nullable|file|mimes:pdf,doc,docx',
            'file3' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 422);
        }

        $company = User::find($companyId);

        if (! $company || $company->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found');
        }

        $companyDetails = $company->companyDetails ?? new CompanyDetail(['user_id' => $company->id]);

        if ($request->hasFile('file')) {
            $companyDetails->file = HelperFunc::uploadFile('files', $request->file('file'));

            $companyDetails->count_offer++;
        }

        if ($request->hasFile('file2')) {
            $companyDetails->file2 = HelperFunc::uploadFile('files', $request->file('file2'));
            $companyDetails->count_offer++;
        }

        if ($request->hasFile('file3')) {
            $companyDetails->file3 = HelperFunc::uploadFile('files', $request->file('file3'));
            $companyDetails->count_offer++;
        }

        if ($companyDetails->count_offer > 3) {
            $companyDetails->status = 1;
        }

        $companyDetails->save();

        return HelperFunc::sendResponse(200, __('messages.updated_successfully'));

    }
    public function updateExpiredDate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'expired_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $company = User::find($id);

        if (! $company || $company->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found');
        }

        $companyDetails           = $company->companyDetails ?? new CompanyDetail(['user_id' => $company->id]);
        $companyDetails->exp_date = $request->expired_date;
        $companyDetails->save();

        return HelperFunc::sendResponse(200, __('messages.updated_successfully'));
    }

    public function addType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id'    => 'required|exists:types,id',
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages());
        }

        $user = User::find($request->company_id);

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $user->typesComapny()->attach($request->type_id);

        return HelperFunc::sendResponse(200, 'Type added successfully', []);
    }

    public function deleteType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id'    => 'required|exists:types,id',
            "company_id" => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages()->all());
        }

        $user = User::find($request->company_id);

        if (! $user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        if (! $user->typesComapny()->where('type_id', $request->type_id)->exists()) {
            return HelperFunc::sendResponse(404, 'Type not found', []);
        }

        // dont deleted if a last type
        if ($user->typesComapny()->count() <= 1) {
            return HelperFunc::sendResponse(400, 'You have to keep at least one type', ['You have to keep at least one type']);
        }

        $user->typesComapny()->detach($request->type_id);

        return HelperFunc::sendResponse(200, 'Type deleted successfully', []);
    }

    public function gityourType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages());
        }

        $user = User::find($request->company_id);

        if (! $user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $takenTypeIds = $user->typesComapny->pluck('id')->toArray();

        // Get available types (not taken by company) with hierarchy
        $notTakenTypes = Type::whereNotIn('id', $takenTypeIds)
            ->where('is_active', true)
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        return HelperFunc::sendResponse(200, '', $notTakenTypes);
    }

    public function TypesHaveing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages());
        }
        $user = User::find($request->company_id);

        if (! $user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $takenTypeIds = $user->typesComapny->pluck('id')->toArray();

        // Get types that company has with hierarchy
        $takenTypes = Type::whereIn('id', $takenTypeIds)
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        return HelperFunc::sendResponse(200, '', $takenTypes);

    }

    // ==================== Countries Methods ====================

    public function getAvailableCountries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $takenCountryIds = $user->countries->pluck('id')->toArray();
        $notTakenCountries = Country::whereNotIn('id', $takenCountryIds)->get();

        return HelperFunc::sendResponse(200, 'Available countries fetched successfully', $notTakenCountries);
    }

    public function addCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        if ($user->countries()->where('country_id', $request->country_id)->exists()) {
            return HelperFunc::sendResponse(400, 'Country already added', []);
        }

        $user->countries()->attach($request->country_id);

        return HelperFunc::sendResponse(200, 'Country added successfully', []);
    }

    public function deleteCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        if (!$user->countries()->where('country_id', $request->country_id)->exists()) {
            return HelperFunc::sendResponse(404, 'Country not found in company list', []);
        }

        // Delete country and all its cities
        $user->countries()->detach($request->country_id);
        $country = Country::find($request->country_id);
        if ($country) {
            $cityIds = $country->cities->pluck('id')->toArray();
            $user->cities()->detach($cityIds);
        }

        return HelperFunc::sendResponse(200, 'Country deleted successfully', []);
    }

    public function getSubscribedCountries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $subscribedCountries = $user->countries()->with('cities')->get();

        return HelperFunc::sendResponse(200, 'Subscribed countries fetched successfully', $subscribedCountries);
    }

    // ==================== Cities Methods ====================

    public function getAvailableCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $takenCityIds = $user->cities->pluck('id')->toArray();
        $query = City::whereNotIn('id', $takenCityIds);

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $notTakenCities = $query->with('country')->get();

        return HelperFunc::sendResponse(200, 'Available cities fetched successfully', $notTakenCities);
    }

    public function addCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities,id',
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $city = City::find($request->city_id);
        if (!$city) {
            return HelperFunc::sendResponse(404, 'City not found', []);
        }

        // Check if country is subscribed first
        if (!$user->countries()->where('country_id', $city->country_id)->exists()) {
            return HelperFunc::sendResponse(400, 'Company must subscribe to the country first', []);
        }

        if ($user->cities()->where('city_id', $request->city_id)->exists()) {
            return HelperFunc::sendResponse(400, 'City already added', []);
        }

        $user->cities()->attach($request->city_id);

        return HelperFunc::sendResponse(200, 'City added successfully', []);
    }

    public function deleteCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities,id',
            'company_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        if (!$user->cities()->where('city_id', $request->city_id)->exists()) {
            return HelperFunc::sendResponse(404, 'City not found in company list', []);
        }

        $user->cities()->detach($request->city_id);

        return HelperFunc::sendResponse(200, 'City deleted successfully', []);
    }

    public function getSubscribedCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = User::find($request->company_id);
        if (!$user || $user->role !== 'company') {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $query = $user->cities()->with('country');

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $subscribedCities = $query->get();

        return HelperFunc::sendResponse(200, 'Subscribed cities fetched successfully', $subscribedCities);
    }

}
