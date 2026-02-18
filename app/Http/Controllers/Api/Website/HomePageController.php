<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\HomePageResource;
use App\Mail\OfferCreated;
use App\Mail\OfferSelling;
use App\Models\ConfigApp;
use App\Models\Faq;
use App\Models\Form;
use App\Models\Home;
use App\Models\Offer;
use App\Models\PartnerPage;
use App\Models\ReviewCompany;
use App\Models\ReviewSite;
use App\Models\Shopping_list;
use App\Models\Type;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Location;

class HomePageController extends Controller
{
    public function index(Request $request)
    {
        // Set language for the application based on the user's request
        $language = $request->get('lang', default: 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        // Fetch the data based on language
        $data['home']        = Home::with('media')->first()?->makeHidden(['created_at', 'updated_at']);
        $data['form']        = Form::first()?->makeHidden(['created_at', 'updated_at']);
        $data['faq']         = Faq::take(5)->get()?->makeHidden(['created_at', 'updated_at']);
        $data['partnerPage'] = PartnerPage::first()?->makeHidden(['created_at', 'updated_at']);

        // Fetch services hierarchically (parents with children)
        $data['services'] = Type::with([
            'typeDitaliServices' => function ($query) {
                $query->select('id', 'type_id', 'short_description', 'small_image', 'slug', 'service_home_icon');
            },
            'typeDitaliServices.media',
            'children' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'children.typeDitaliServices' => function ($query) {
                $query->select('id', 'type_id', 'short_description', 'small_image', 'slug', 'service_home_icon');
            },
            'children.typeDitaliServices.media'
        ])
            ->whereNull('parent_id') // Only parent types
            ->where('is_active', true)
            ->orderBy('order')
            ->select('id', 'name', 'price', 'parent_id', 'order')
            ->get()
            ->map(function ($item) {
                return $item->makeHidden(['created_at', 'updated_at']);
            });

        $data['reviewCompany'] = ReviewCompany::where('status', '1')
            ->select('comment', 'email', 'name', 'stars')
            ->take(5)
            ->get();

        $data['reviewSite'] = ReviewSite::where('status', '1')
            ->select('comment', 'email', 'name', 'stars')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return $item->makeHidden(['created_at', 'updated_at']);
            });

        // Fetch companies and apply language logic
        $data['companeis'] = User::with('companyDetails')
            ->where('ban', 0)
            ->withCount('shopping_list')
            ->whereHas('companyDetails', function ($q) {
                $q->where('sucsses', '1');
            })
            ->orderBy('shopping_list_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return $item->makeHidden(['created_at', 'updated_at']);
            });

        // Return the response with localized data
        return HelperFunc::sendResponse(200, 'done', new HomePageResource((object) $data));
    }

    public function store(Request $request)
    {
        $status = true;

        $config = ConfigApp::first();

        if ($config->offer_flow == 1) {
            $status = false;
        }

        if ($config->add_offer == 1) {
            return HelperFunc::apiResponse(true, 200, ['message' => 'Offer Add Is Stoping']);
        }

        $Validator = Validator::make($request->all(), [
            'type_id'        => 'required|exists:types,id',
            'country_id'     => 'required|exists:countries,id',
            'city_id'        => 'required|exists:cities,id',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'date'           => 'required|date',
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
            'execution_date' => 'required|date',
            'person_type'    => 'required|string|max:255',
            'Besonderheiten' => 'nullable|string|max:255',
            'lang'           => 'nullable|string|max:255',
            'country'        => 'nullable|string|max:255',
            'zipcode'        => 'nullable|string|max:255',
            'Nach_country'   => 'nullable|string|max:255',
            'Nach_zipcode'   => 'nullable|string|max:255',
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
            $confirmToken = Str::random(64);

            $data = [
                'type_id'          => $request->type_id,
                'country_id'       => $request->country_id,
                'city_id'          => $request->city_id,
                'name'             => $request->name,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'date'             => $request->date,
                'adresse'          => $request->adresse,
                'ort'              => $request->ort,
                'zimmer'           => $request->zimmer,
                'etage'            => $request->etage,
                'zipcode'          => $request->zipcode,
                'carrent_country'  => $request->carrent_country,
                'vorhanden'        => $request->vorhanden,
                'Nach_Adresse'     => $request->Nach_Adresse ?? null,
                'Nach_Ort'         => $request->Nach_Ort ?? null,
                'Nach_Zimmer'      => $request->Nach_Zimmer ?? null,
                'Nach_Etage'       => $request->Nach_Etage ?? null,
                'Nach_vorhanden'   => $request->Nach_vorhanden ?? null,
                'Nach_country'     => $request->Nach_country ?? null,
                'Nach_zipcode'     => $request->Nach_zipcode ?? null,
                'count'            => $request->count,
                'execution_date'   => $request->execution_date,
                'person_type'      => $request->person_type,
                'Number_of_offers' => $request->count,
                'Besonderheiten'   => $this->filterBesonderheiten($request->Besonderheiten ?? null),
                'ip'               => $this->getClientIp($request),
                'country'          => $request->country ?? $this->getCountryFromIP($this->getClientIp($request)),
                'city'             => $request->city ?? $this->getCityFromIP($this->getClientIp($request)),
                'lang'             => $request->lang,
                'cheek'            => true,
                'status'           => $status,
                'confirm_status'   => 'pending',
                'confirm_token'    => $confirmToken,
                'confirmed_at'     => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $offerId = DB::table('offers')->insertGetId($data);
            $offer   = DB::table('offers')->where('id', $offerId)->first();

            // send email to user with confirmation link (web route, no /api prefix)
            $locale     = $request->lang;
            $confirmUrl = url('/user/offers/confirm/' . $confirmToken);

            Mail::to($request->email)->send(new OfferCreated($locale, $confirmUrl));

            $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
            HelperFunc::sendMultilangNotification($admins, "new_offer_created", $offer->id, [
                'en' => 'A new offer "' . $offer->name,
                'de' => 'Ein neues Angebot "' . $offer->name,
            ]);
            return HelperFunc::sendResponse(201, 'Offer created successfully. Please confirm your offer from the email.', $offer);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    private function getCountryFromIP($ip)
    {
        $position = Location::get($ip);
        return $position->countryName ?? 'Unknown';
    }

    private function getClientIp(Request $request)
    {
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            // إذا كان هناك أكثر من IP مفصولين بفواصل، خذ أول واحد
            $ip = explode(',', $ip)[0];
        } else {
            $ip = $request->ip();
        }
        return trim($ip);
    }

    private function getCityFromIP($ip)
    {
        $position = Location::get($ip);
        return $position->cityName ?? 'Unknown';
    }

    // Sanitize 'Besonderheiten' field
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

    public function storeReivewSite(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'stars'   => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $validatedData->messages()->all());
        }
        $review = ReviewSite::create($validatedData->validated());

        return HelperFunc::sendResponse(201, 'Review added successfully', $review);
    }

    public function confirmOffer($token)
    {
        $offer = Offer::where('confirm_token', $token)->first();

        if (! $offer || $offer->confirm_status === 'confirmed') {
            // لو الأوفر مش موجود أو متأكد قبل كده نرجعه برضه لصفحة الـ frontend
            return redirect('https://auftragkompass.de/en/confirm-offer');
        }

        DB::beginTransaction();
        try {
            $offer->confirm_status = 'confirmed';
            $offer->confirm_token  = null;
            $offer->confirmed_at   = now();
            $offer->save();

            $this->bayOffer($offer->id);

            DB::commit();
            // بعد نجاح التأكيد والبيع الديناميك نرجع المستخدم لصفحة التأكيد في الـ Frontend
            return redirect('https://auftragkompass.de/en/confirm-offer');
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, 'An error occurred while confirming the offer: ' . $e->getMessage(), []);
        }
    }

    private function bayOffer($offer_id)
    {

        $offer = Offer::find($offer_id);

        if (! $offer) {
            // يمكنك إرسال رسالة خطأ مفيدة هنا أو تجاهل العملية
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        $today = now()->format('Y-m-d');

        $companies = User::where('role', 'company')
            ->where('ban', '0')
            ->where('status', '1')
            ->whereHas('companyDetails', function ($query) {
                $query->where('sucsses', '1');
            })
            ->whereHas('typesComapny', function ($query) use ($offer) {
                $query->where('type_id', $offer->type_id);
            })
            ->withCount([
                'shopping_list as shopping_list_count' => function ($query) use ($today) {
                    $query->where('type', 'D')
                        ->whereDate('created_at', $today);
                },
            ])
            ->get();

        $companies->each(function ($company) {
            $company->shopping_list_count = $company->shopping_list_count ?? 0;
        });

        $filteredCompanies = $companies->filter(function ($company) use ($offer) {
            $amountTotal        = $company->wallet->amount ?? 0;
            $expenseTotal       = $company->wallet->expense ?? 0;
            $totalMoneyInWallet = $amountTotal - $expenseTotal;

            return $totalMoneyInWallet >= ($offer->type->price / $offer->Number_of_offers);
        });

        $sortedCompanies = $filteredCompanies->sortBy('shopping_list_count');

        foreach ($sortedCompanies as $company) {

            Shopping_list::create([
                'offer_id' => $offer->id,
                'user_id'  => $company->id,
                'type'     => 'D', // نوع الشراء
            ]);

            // إرسال إشعار للدفع
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
            $company->notify(new PaymentNotification($data));

            $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
            HelperFunc::sendMultilangNotification($admins, 'offer_purchased', $offer->id, [
                'en' => 'The offer "' . $offer->name . '" has been purchased by the company "' . $company->name . '" for ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF.',
                'de' => 'Das Angebot "' . $offer->name . '" wurde von der Firma "' . $company->name . '" für ' . ($offer->type->price / $offer->Number_of_offers) . ' CHF gekauft.',
            ]);
            $this->sendMailtocompanyDataOfOffer($company, $offer);
            // تحديث عدد العروض المتاحة
            $offer->decrement('count');

            // تحديث محفظة المستخدم
            $company->wallet()->updateOrCreate(
                [],
                ['expense' => $company->wallet->expense + $offer->type->price]
            );
            // إرسال بريد إلكتروني للشركة
        }
    }

    private function sendMailtocompanyDataOfOffer($user, $offer)
    {
        $locale = $user->lang ?? 'en';
        Mail::to($user->email)->send(new OfferSelling($offer));
    }
}
