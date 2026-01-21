<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyDetail;
use App\Models\ConfigApp;
use App\Models\Offer;
use App\Models\Shopping_list;
use App\Models\Type;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use App\Notifications\PaymentNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use function PHPSTORM_META\type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CompanyProfileController extends Controller
{

    public function show($id)
    {
        $company = User::with(['companyDetails', 'wallet', 'review', 'typesComapny'])->find($id);

        if (! $company || $company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $company->img                   = $company->img ? asset($company->img) : $company->img                   = 'https://ui-avatars.com/api/?name=' . $company->name;
        $company->companyDetails->file  = $company->companyDetails->file ? asset($company->companyDetails->file) : null;
        $company->companyDetails->file2 = $company->companyDetails->file2 ? asset($company->companyDetails->file2) : null;
        $company->companyDetails->file3 = $company->companyDetails->file3 ? asset($company->companyDetails->file3) : null;

        return response()->json(['company' => $company], 200);
    }

    public function profile_auth()
    {
        $user = Auth::user();

        $user->with('companyDetails', 'shopping_list')->get();
        // $user->shopping_list->count()
        $data = [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'email'                => $user->email,
            'phone'                => $user->phone,
            'company_approved'     => $user->companyDetails->sucsses,
            'company_offers_count' => $user->shopping_list->count(),
            'image'                => asset($user->img),

        ];

        return HelperFunc::sendResponse(200, 'User profile retrieved successfully', $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string',
            'phone'        => 'nullable|string',
            'phone2'       => 'nullable|string',
            'email'        => 'nullable|email|unique:users,email,' . $id,
            'img'          => 'nullable|file|mimes:jpg,jpeg,png',
            'address'      => 'nullable|string',
            'website'      => 'nullable|string',
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
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages()->all());
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

        $companyDetails = $company->companyDetails ?? new CompanyDetail(['user_id' => $company->id]);

        $detailsFields = ['address', 'website', 'about', 'number', 'phone2', 'description', 'banc_ip', 'banc_count', 'banc_name', 'founded_year'];
        foreach ($detailsFields as $field) {
            if ($request->has($field)) {
                $companyDetails->$field = $request->$field;
            }
        }

        $files = ['file', 'file2', 'file3'];
        $links = $companyDetails->files_links ?? [];

        if (is_string($links)) {
            $links = json_decode($links, true);
        }

        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $link                  = HelperFunc::uploadFile('files', $request->file($file));
                $links[]               = $link;
                $companyDetails->$file = $link;
                $companyDetails->count_offer++;
            }

            HelperFunc::sendMultilangNotification(User::query()->where('role', 'admin')->where("available_notification", '1')->get(), "companyAddContract", $company->id, [
                'en' => ' company added Contract : ' . $company->name,
                'de' => 'Firmenvertrag hinzugefügt : ' . $company->name,
            ]);
        }

        $companyDetails->files_links = $links;

        if ($companyDetails->count_offer > 3) {
            $companyDetails->status = 1;
        }

        $companyDetails->save();

        return HelperFunc::sendResponse(200, __('messages.updated_successfully'));
    }

    public function updateDinamicOfferCompany($status)
    {
        // Get authenticated user
        $company = Auth::user();

        // Check role
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }

        // Validate status
        if (! in_array($status, [0, 1])) {
            return response()->json(['message' => 'Invalid status value'], 400);
        }

        // Update company status
        $company->status = $status;
        $company->save();

        // Get app config
        $config = ConfigApp::first();

        // If active or dynamic offer is off, run offer purchase logic
        if ($company->status == 1 || $config->accept_dynamic_offer == 0) {
            $this->bayOfferCompany();
        }

        // Get all admins with notifications enabled
        $admins = User::where("role", "admin")
            ->where('available_notification', '1')
            ->get();

        // Prepare status message
        $statusText = $status == 1 ? 'activated' : 'deactivated';

        // Send notification to admins
        Notification::send($admins, new \App\Notifications\PaymentNotification([
            'type'    => "companyDynamicBuyOffer",
            'type_id' => $company->id,
            'mgs'     => [
                'en' => 'The company ' . $company->name . ' has ' . $statusText . ' Dynamic Buy Offer.',
                'de' => 'Die Firma ' . $company->name . ' hat ' . ($status == 1 ? 'aktiviert' : 'deaktiviert') . ' Dynamic Buy Offer.'],
        ]));

        // Response
        return HelperFunc::sendResponse(200, 'Company status updated successfully', []);
    }

    private function bayOfferCompany()
    {
        $today = now()->format('Y-m-d'); // Get today's date in Y-m-d format
        $now   = now();                  // Get the current timestamp

        // Fetch offers that:
        // - are scheduled for 1 day before today
        // - are before the current time
        // - still have available quantity
        $offers = Offer::whereRaw('DATE_SUB(date, INTERVAL 1 DAY) = ?', [$today])
            ->where('date', '<', $now)
            ->where('count', '!=', 0)
            ->get();

        // Get the currently authenticated company
        $company = Auth::user();

        if (! $company || ! $company->wallet) {
            return; // No authenticated user or wallet associated
        }

        // Calculate the available balance in the company's wallet
        $amountTotal        = $company->wallet->amount;
        $expenseTotal       = $company->wallet->expense;
        $totalMoneyInWallet = $amountTotal - $expenseTotal;

        foreach ($offers as $offer) {
            // Check if the offer was already purchased by the company
            $alreadyPurchased = Shopping_list::where('user_id', $company->id)
                ->where('offer_id', $offer->id)
                ->exists();

            if ($alreadyPurchased) {
                continue; // Skip if already purchased
            }

            // Check if company has enough funds to purchase the offer
            if ($totalMoneyInWallet >= $offer->price) {
                // Add offer to shopping list
                Shopping_list::create([
                    'offer_id' => $offer->id,
                    'user_id'  => $company->id,
                    'type'     => 'D',
                ]);

                // Decrease the offer quantity by 1
                $offer->decrement('count');

                // Prepare notification data
                $data = [
                    'type'     => 'offer',
                    'offer_id' => $offer->id,
                    'mgs'      => [
                        'en' => 'You have a new offer with: ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF',
                        'de' => 'Sie haben ein neues Angebot mit: ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF',
                        'it' => 'Hai una nuova offerta con: ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF',
                        'fr' => 'Vous avez une nouvelle offre avec: ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF',
                    ],
                    'serves'   => $offer->type->getTranslations('name'),
                ];

                // Notify the company
                $company->notify(new PaymentNotification($data));

                // Update the wallet expense for the company
                $company->wallet()->updateOrCreate(
                    ['user_id' => $company->id],
                    ['expense' => $company->wallet->expense - $offer->type->price / $offer->Number_of_offers]
                );

                // Notify all admins about the offer purchase
                $admins = User::query()
                    ->where('role', 'admin')
                    ->where("available_notification", '1')
                    ->get();

                HelperFunc::sendMultilangNotification($admins, 'offer_purchased', $offer->id, [
                    'en' => 'The offer "' . $offer->name . '" has been purchased by the company "' . $company->name . '" for ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF.',
                    'de' => 'Das Angebot "' . $offer->name . '" wurde von der Firma "' . $company->name . '" für ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF gekauft.',
                ]);

                // Deduct the offer price from the available wallet money
                $totalMoneyInWallet -= $offer->price;
            }
        }
    }

    public function getStatusAuth()
    {
        $user = Auth::user();
        if ($user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }
        return HelperFunc::sendResponse(200, 'OK', $user->companyDetails->status);
    }
    public function gityourType()
    {
        $user = auth()->user();

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $takenTypeIds = $user->typesComapny->pluck('id')->toArray();

        // Get available types (not taken by user) with hierarchy
        $notTakenTypes = Type::whereNotIn('id', $takenTypeIds)
            ->where('is_active', true)
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        return HelperFunc::sendResponse(200, '', $notTakenTypes);
    }

    public function addType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages());
        }

        $user = Auth::user();

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $user->typesComapny()->attach($request->type_id);
        $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();

        $type = type::find($request->type_id);
        if (! $type) {
            return HelperFunc::sendResponse(404, 'Type not found', []);
        }
        HelperFunc::sendMultilangNotification($admins, "type", $user->id, [
            'en' => 'New type added : ' . $type->name['en'],
            'de' => 'Neuer Typ hinzugefügt : ' . $type->name['de'],
        ]);

        return HelperFunc::sendResponse(200, 'Type added successfully', []);
    }

    public function deleteType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
        ]);
        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'errors', $validator->messages()->all());
        }

        $user = Auth::user();

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        // dont deleted if a last type
        if ($user->typesComapny()->count() <= 1) {
            return HelperFunc::sendResponse(400, 'You have to keep at least one type', ['You have to keep at least one type']);
        }

        $user->typesComapny()->detach($request->type_id);

        return HelperFunc::sendResponse(200, 'Type deleted successfully', []);
    }
    public function getSubscribedTypes()
    {
        $user = Auth::user();
        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $subscribedTypes = $user->typesComapny;

        return HelperFunc::sendResponse(200, 'User subscribed types fetched successfully', $subscribedTypes);
    }

    public function generateContractPdf(Request $request)
    {
        // Retrieve the user based on company ID provided in the request
        $user = User::findOrFail($request->company_id);

        // Retrieve company details (ensure the 'companyDetails' relation is defined in your User model)
        $companyDetails = $user->companyDetails;
        // Get confing Application details
        $config = ConfigApp::first();
        // Get language from header; default to 'en' if not provided
        $lang = $request->lang;

        // Prepare data to pass to the Blade view
        $data = [
            'company_name'   => $user->name ?? 'Musterfirma GmbH',
            'created_at'     => $user->created_at ?? now(),
            'img'            => $config->logo_dark ? asset($config->logo_dark) : null,
            'contact_person' => $companyDetails->owner_name ?? '__________',
            'address'        => $companyDetails->address ?? '__________',
            'phone'          => $companyDetails->number ?? '__________',
            'mobile'         => $user->phone ?? '__________',
            'email'          => $user->email ?? '__________',
            'website'        => $companyDetails->website ?? '__________',
            'ZIPCode'        => $companyDetails->ZIPCode ?? '__________',
            'trade_register' => $companyDetails->reg_name ?? '__________',
            'lang'           => $lang, // Use language from header
        ];

        // Generate the PDF using the Blade template
        $pdf = Pdf::loadView('pdf.contract_template', $data);

        return $pdf->download('contract.pdf');
    }

    // ==================== Countries Methods ====================

    public function getAvailableCountries()
    {
        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $takenCountryIds = $user->countries->pluck('id')->toArray();
        $notTakenCountries = Country::whereNotIn('id', $takenCountryIds)->get();

        return HelperFunc::sendResponse(200, 'Available countries fetched successfully', $notTakenCountries);
    }

    public function addCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        // Check if already exists
        if ($user->countries()->where('country_id', $request->country_id)->exists()) {
            return HelperFunc::sendResponse(400, 'Country already added', []);
        }

        $user->countries()->attach($request->country_id);

        $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
        $country = Country::find($request->country_id);
        if ($country) {
            HelperFunc::sendMultilangNotification($admins, "country_added", $user->id, [
                'en' => 'New country added: ' . ($country->name['en'] ?? ''),
                'de' => 'Neues Land hinzugefügt: ' . ($country->name['de'] ?? ''),
            ]);
        }

        return HelperFunc::sendResponse(200, 'Country added successfully', []);
    }

    public function deleteCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        if (!$user->countries()->where('country_id', $request->country_id)->exists()) {
            return HelperFunc::sendResponse(404, 'Country not found in your list', []);
        }

        // Delete country and all its cities
        $user->countries()->detach($request->country_id);
        // Also detach all cities from this country
        $country = Country::find($request->country_id);
        if ($country) {
            $cityIds = $country->cities->pluck('id')->toArray();
            $user->cities()->detach($cityIds);
        }

        return HelperFunc::sendResponse(200, 'Country deleted successfully', []);
    }

    public function getSubscribedCountries()
    {
        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $subscribedCountries = $user->countries()->with('cities')->get();

        return HelperFunc::sendResponse(200, 'Subscribed countries fetched successfully', $subscribedCountries);
    }

    // ==================== Cities Methods ====================

    public function getAvailableCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $takenCityIds = $user->cities->pluck('id')->toArray();
        $query = City::whereNotIn('id', $takenCityIds);

        // Filter by country if provided
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
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $city = City::find($request->city_id);
        if (!$city) {
            return HelperFunc::sendResponse(404, 'City not found', []);
        }

        // Check if country is subscribed first
        if (!$user->countries()->where('country_id', $city->country_id)->exists()) {
            return HelperFunc::sendResponse(400, 'You must subscribe to the country first', []);
        }

        // Check if already exists
        if ($user->cities()->where('city_id', $request->city_id)->exists()) {
            return HelperFunc::sendResponse(400, 'City already added', []);
        }

        $user->cities()->attach($request->city_id);

        $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
        HelperFunc::sendMultilangNotification($admins, "city_added", $user->id, [
            'en' => 'New city added: ' . ($city->name['en'] ?? ''),
            'de' => 'Neue Stadt hinzugefügt: ' . ($city->name['de'] ?? ''),
        ]);

        return HelperFunc::sendResponse(200, 'City added successfully', []);
    }

    public function deleteCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        if (!$user->cities()->where('city_id', $request->city_id)->exists()) {
            return HelperFunc::sendResponse(404, 'City not found in your list', []);
        }

        $user->cities()->detach($request->city_id);

        return HelperFunc::sendResponse(200, 'City deleted successfully', []);
    }

    public function getSubscribedCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages());
        }

        $user = Auth::user();
        if (!$user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        $query = $user->cities()->with('country');

        // Filter by country if provided
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $subscribedCities = $query->get();

        return HelperFunc::sendResponse(200, 'Subscribed cities fetched successfully', $subscribedCities);
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

        $country = Country::with('cities')->find($country_id);
        
        if (!$country) {
            return HelperFunc::sendResponse(404, 'Country not found', []);
        }

        return HelperFunc::sendResponse(200, 'Cities fetched successfully', [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
            ],
            'cities' => $country->cities
        ]);
    }

}
