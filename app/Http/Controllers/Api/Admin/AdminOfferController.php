<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferWithAnswersResource;
use App\Models\ConfigApp;
use App\Models\Offer;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Location;

class AdminOfferController extends Controller
{

    public function getFilteredOffers(Request $request)
    {
        try {
            // Get today's date and current time
            $today = Carbon::today()->toDateString();
            $now   = Carbon::now();

            // Get the filter from the request
            $filterType = $request->input('filter'); // e.g., "24_hours", "purchased", "not_purchased"

            // Start the query
            $query = Offer::with('type');

            // Apply filters
            if ($filterType == '24_hours_to_filed') {
                $query->whereRaw('DATE_SUB(date, INTERVAL 1 DAY) = ?', [$today])
                    ->where('date', '<', $now)
                    ->where('count', '!=', 0);
            } elseif ($filterType == 'not_completed') {
                $query->whereHas('Shopping_list');
            } elseif ($filterType == 'new') {
                $query->doesntHave('Shopping_list');
            } elseif ($filterType == 'filed') {
                $query->where(function ($q) use ($now) {
                    $q->where('date', '<', $now)
                        ->where('count', '!=', 0);
                });
            } elseif ($filterType == 'completed') {
                $query->where(function ($q) use ($now) {
                    $q->where('date', '<', $now)
                        ->where('count', '=', 0);
                });
            }

            // Paginate the filtered offers
            $offers = $query->orderBy('id', 'desc')->paginate(10); // 10 offers per page

            // Return paginated offers using the helper
            return HelperFunc::pagination($offers, $offers->items());
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while fetching the offers: ' . $e->getMessage(), []);
        }
    }

    // Show a specific offer
    public function show($id)
    {
        $offer = Offer::with([
            'type',
            'countryRelation',
            'cityRelation',
            'answers.question',
            'answers.options',
            'answers.files'
        ])->findOrFail($id); // Get the offer by ID with questions and answers

        return new OfferWithAnswersResource($offer);
    }

    public function store(Request $request)
    {
        $status = true; //;
        $config = ConfigApp::first();
        if ($config->offer_flow == 1) {
            $status = false;
        }

        if ($config->add_offer == 1) {
            return HelperFunc::apiResponse(true, 200, ['message' => 'Offer Add Is  Stoping']);
        }

        $Validator = Validator::make($request->all(), [
            'type_id'        => 'required|exists:types,id',
            'country_id'     => 'required|exists:countries,id',
            'city_id'        => 'required|exists:cities,id',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'date'           => 'nullable|date',
            'adresse'        => 'required|string|max:255',
            'ort'            => 'required|string|max:255',
            'zimmer'         => 'required|string|max:255',
            'etage'          => 'required|string|max:255',
            'vorhanden'      => 'required|string|max:255',
            'Nach_Adresse'   => 'nullable|string|max:255',
            'Nach_Ort'       => 'nullable|string|max:255',
            'Nach_Zimmer'    => 'nullable|string|max:255',
            'Nach_Etage'     => 'nullable|string|max:255',
            'Nach_vorhanden' => 'nullable|string|max:255',
            'count'          => 'required|integer|min:0',
            'Besonderheiten' => 'nullable|string|max:255',
            'lang'           => 'nullable|string|max:255',

        ]);

        // Validate that city belongs to country
        if ($request->has('country_id') && $request->has('city_id')) {
            $city = \App\Models\City::find($request->city_id);
            if ($city && $city->country_id != $request->country_id) {
                return HelperFunc::sendResponse(422, 'Validation Error', [
                    'city_id' => ['The selected city does not belong to the selected country.']
                ]);
            }
        }

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $Validator->messages()->all());
        }
        try {
            // حساب سعر البيع (لكل شركة) بناءً على سعر الخدمة الحالي وعدد الشركات
            $type         = Type::find($request->type_id);
            $typePrice    = $type?->price ?? 0;
            $numberOffers = max(1, (int) $request->count);
            $unitPrice    = $numberOffers > 0 ? $typePrice / $numberOffers : $typePrice;

            // Create the offer
            $offer = Offer::create([
                'type_id'          => $request['type_id'],
                'country_id'       => $request['country_id'],
                'city_id'          => $request['city_id'],
                'name'             => $request['name'],
                'email'            => $request['email'],
                'phone'            => $request['phone'],
                'date'             => $request['date'],
                'adresse'          => $request->adresse,
                'ort'              => $request->ort,
                'zimmer'           => $request['zimmer'],
                'etage'            => $request['etage'],
                'vorhanden'        => $request['vorhanden'],
                'Nach_Adresse'     => $request['Nach_Adresse'] ?? null,
                'Nach_Ort'         => $request['Nach_Ort'] ?? null,
                'Nach_Zimmer'      => $request['Nach_Zimmer'] ?? null,
                'Nach_Etage'       => $request['Nach_Etage'] ?? null,
                'Nach_vorhanden'   => $request['Nach_vorhanden'] ?? null,
                'count'            => $request['count'],
                'Number_of_offers' => $request['count'],
                'unit_price'       => $unitPrice,
                'Besonderheiten'   => $this->filterBesonderheiten($request['Besonderheiten'] ?? null),
                'ip'               => $request->ip(),
                'country'          => $request['country'] ?? $this->getCountryFromIP($request->ip()),
                'city'             => $request['city'] ?? $this->getCityFromIP($request->ip()),
                'lang'             => $request['lang'],
                'cheek'            => true,    // Default value
                'status'           => $status, // Default value
            ]);

            // Return a success response
            return HelperFunc::sendResponse(201, 'Offer created successfully', $offer);
        } catch (\Exception $e) {
            // Return an error response in case of exceptions
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    private function getCountryFromIP($ip)
    {
        $position = Location::get($ip);
        return $position->countryName ?? 'Unknown';
    }

    private function getCityFromIP($ip)
    {
        $position = Location::get($ip);
        return $position->cityName ?? 'Unknown';
    }

    private function filterEmail($email)
    {
        // Mask part of the email (example: mask the first part of the email)
        $emailParts = explode('@', $email);
        if (isset($emailParts[0])) {
            $emailParts[0] = str_repeat('*', strlen($emailParts[0])); // Mask the first part of the email
        }
        return implode('@', $emailParts);
    }

    private function filterBesonderheiten($besonderheiten)
    {
        // التعبيرات المنتظمة لتعريف الأنماط
        $patterns = [
            // رقم الهاتف أو الهاتف الأرضي (بأشكال متعددة)
            '/\b(\+?\d{1,3}[-.\s]?)?(\(?\d{1,4}\)?[-.\s]?)?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}\b/',

            // عناوين البريد الإلكتروني
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/',

            // عناوين الشوارع (بسيطة مثل: "123 Main St" أو "42 Elm Street")
            '/\b\d+\s[A-Za-z]+\s(?:Street|St|Avenue|Ave|Road|Rd|Lane|Ln|Boulevard|Blvd|Drive|Dr|Court|Ct|Way|Square|Sq|Place|Pl|Terrace|Terr|Parkway|Pkwy)\b/i',

            // أي نمط آخر يشتبه في كونه عنوانًا أو رقمًا
            '/\b\d+\s[A-Za-z]+\b/', // مثل "123 Main"
        ];

        // استبدال جميع الأنماط بـ *****
        return preg_replace($patterns, '*****', $besonderheiten);
    }

    // Update an existing offer
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'type'             => 'sometimes|required|string',
            'country_id'       => 'sometimes|required|exists:countries,id',
            'city_id'          => 'sometimes|required|exists:cities,id',
            'anrede'           => 'nullable|string',
            'name'             => 'sometimes|required|string',
            'email'            => 'sometimes|required|email',
            'phone'            => 'sometimes|required|string',
            'date'             => 'nullable|date',
            'adresse'          => 'sometimes|required|string',
            'ort'              => 'sometimes|required|string',
            'zimmer'           => 'sometimes|required|string',
            'etage'            => 'sometimes|required|string',
            'vorhanden'        => 'sometimes|required|string',
            'Nach_Adresse'     => 'nullable|string',
            'Nach_Ort'         => 'nullable|string',
            'Nach_Zimmer'      => 'nullable|string',
            'Nach_Etage'       => 'nullable|string',
            'Nach_vorhanden'   => 'nullable|string',
            'count'            => 'sometimes|required|integer',
            'Number_of_offers' => 'nullable|integer',
            'cheek'            => 'nullable|boolean',
            'Besonderheiten'   => 'nullable|string',
        ]);

        // Validate that city belongs to country if both are provided
        if ($request->has('country_id') && $request->has('city_id')) {
            $city = \App\Models\City::find($request->city_id);
            if ($city && $city->country_id != $request->country_id) {
                return HelperFunc::sendResponse(422, 'Validation Error', [
                    'city_id' => ['The selected city does not belong to the selected country.']
                ]);
            }
        }

        $offer = Offer::findOrFail($id); // Get the offer by ID

        $offer->update($validatedData); // Update the offer
        return response()->json($offer);
    }

    /**
     * تحديث سعر البيع (unit_price) لأوفر معيّن من لوحة السوبر أدمن
     */
    public function updateUnitPrice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $offer = Offer::findOrFail($id);
        $offer->unit_price = $request->unit_price;
        $offer->save();

        return HelperFunc::sendResponse(200, 'Unit price updated successfully', [
            'offer_id'   => $offer->id,
            'unit_price' => $offer->unit_price,
        ]);
    }

    // Delete an offer
    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);            // Get the offer by ID
        $offer->delete();                           // Delete the offer
        return response()->json(null, status: 204); // Return empty response
    }

    public function updateStatus($id, $status)
    {

        $offer         = Offer::findOrFail($id);
        $offer->status = $status;
        $offer->save();
        return HelperFunc::sendResponse(200, __('Status updated successfully'), []);
    }
}
