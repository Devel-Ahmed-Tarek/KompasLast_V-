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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Location;
use App\Mail\OfferPurchasedCompany;
use App\Mail\OfferPurchasedAdmin;
use App\Mail\NewOfferForCompany;
use App\Mail\AdminNewOffer;

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

            // احسب سعر البيع (لكل شركة) بناءً على سعر الخدمة الحالي
            $type          = Type::find($request->type_id);
            $typePrice     = $type?->price ?? 0;
            $numberOffers  = max(1, (int) $request->count);
            $unitPrice     = $numberOffers > 0 ? $typePrice / $numberOffers : $typePrice;

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
                'unit_price'       => $unitPrice,
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
            /** @var \App\Models\Offer $offerModel */
            $offerModel = Offer::find($offerId);

            // send email to user with confirmation link (web route, no /api prefix)
            $locale     = $request->lang;
            $confirmUrl = "https://auftragkompass.de/" . $locale . "/confirm-offer/?token=" . $confirmToken;

            Mail::to($request->email)->send(new OfferCreated($locale, $confirmUrl));

            // send informational email to admin that a new offer entered the system
            try {
                $adminEmail = config('mail.admin_info', 'info@auftragkompass.com');
                if ($offerModel && $adminEmail) {
                    Mail::to($adminEmail)->send(new AdminNewOffer($offerModel));
                }
            } catch (\Exception $e) {
                // لا نكسر الفلو لو إيميل الأدمن فشل
            }

            $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
            HelperFunc::sendMultilangNotification($admins, "new_offer_created", $offerModel->id, [
                'en' => 'A new offer "' . $offerModel->name . '" has been created',
                'de' => 'Ein neues Angebot "' . $offerModel->name . '" wurde erstellt',
            ]);
            return HelperFunc::sendResponse(201, 'Offer created successfully. Please confirm your offer from the email.', $offerModel ?? $data);
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
        $config = ConfigApp::first();

        if (! $offer || $offer->confirm_status === 'confirmed') {
            // لو الأوفر مش موجود أو متأكد قبل كده نرجع نفس الرسالة
            return HelperFunc::sendResponse('200', 'success', []);
        }

        DB::beginTransaction();
        try {
            $offer->confirm_status = 'confirmed';
            $offer->confirm_token  = null;
            $offer->confirmed_at   = now();
            $offer->save();

            // لو الأدمن مفعّل الحجب → الأوفر يفضل status=false ومفيش إيميلات
            // الأدمن لازم يعمل Activate يدوياً عشان الإيميلات تتبعت
            if ($config && $config->offer_flow == 1) {
                DB::commit();
                return HelperFunc::sendResponse('200', 'success', []);
            }

            // الأدمن مش حاجب → نوزع الأوفر فوراً
            $this->processOfferDistribution($offer);

            DB::commit();
            return HelperFunc::sendResponse('200', 'success', []);
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, 'An error occurred while confirming the offer: ' . $e->getMessage(), []);
        }
    }

    /**
     * نسخة public من processOfferDistribution لاستدعائها من Controllers تانية
     * (مثل AdminOfferController عند Activate الأوفر)
     */
    public function processOfferDistributionPublic(Offer $offer): void
    {
        $this->processOfferDistribution($offer);
    }

    /**
     * توزيع الأوفر على الشركات المطابقة بمنطق موحد:
     * 1) لو accept_dynamic_offer مفعّل → الشركات اللي عندها status=1 ورصيد كافٍ تشتري تلقائياً
     * 2) الباقيين (شراء تلقائي معطّل أو رصيد ناقص) ياخدوا إشعار وجود أوفر جديد في الشوب
     */
    private function processOfferDistribution(Offer $offer): void
    {
        try {
            $config = ConfigApp::first();

            // لازم يكون للأوفر دولة ومدينة عشان نطابق على اشتراك الشركات
            if (! $offer->country_id || ! $offer->city_id) {
                return;
            }

            $offer->loadMissing('type');

            // 1) جلب كل الشركات المطابقة (نوع + دولة + مدينة + موافقة + غير محظورة)
            //    هنا مش بنفلتر على status لأنه دلالة الشراء التلقائي
            $matchingCompanies = User::where('role', 'company')
                ->where('ban', '0')
                ->whereHas('companyDetails', function ($q) {
                    $q->where('sucsses', '1');
                })
                ->whereHas('typesComapny', function ($q) use ($offer) {
                    $q->where('type_id', $offer->type_id);
                })
                ->whereHas('countries', function ($q) use ($offer) {
                    $q->where('country_id', $offer->country_id);
                })
                ->whereHas('cities', function ($q) use ($offer) {
                    $q->where('city_id', $offer->city_id);
                })
                ->with('wallet')
                ->get();

            if ($matchingCompanies->isEmpty()) {
                return;
            }

            $offerPrice = $offer->unit_price
                ?? (optional($offer->type)->price / max(1, $offer->Number_of_offers ?: 1));

            $purchasedCompanyIds = [];
            $adminEmail          = config('mail.admin_info', 'info@auftragkompass.com');

            // 2) الشراء التلقائي (لو مفعّل عالمياً) للشركات اللي status=1 ورصيدها كافٍ
            if ($config && $config->accept_dynamic_offer == 1) {
                $today = now()->format('Y-m-d');

                $autoBuyCompanies = $matchingCompanies
                    ->where('status', 1)
                    ->sortBy(function ($c) use ($today) {
                        return $c->shopping_list()
                            ->whereDate('created_at', $today)
                            ->where('type', 'D')
                            ->count();
                    });

                foreach ($autoBuyCompanies as $company) {
                    $offer->refresh();
                    if ($offer->count <= 0) {
                        break;
                    }

                    $walletAmount  = $company->wallet->amount ?? 0;
                    $walletExpense = $company->wallet->expense ?? 0;
                    $available     = $walletAmount - $walletExpense;

                    if ($available < $offerPrice) {
                        continue;
                    }

                    try {
                        // تنفيذ عملية الشراء
                        Shopping_list::create([
                            'offer_id' => $offer->id,
                            'user_id'  => $company->id,
                            'type'     => 'D',
                        ]);

                        $offer->decrement('count');

                        $company->wallet()->updateOrCreate(
                            ['user_id' => $company->id],
                            ['expense' => $walletExpense + $offerPrice]
                        );

                        // إشعار داخل التطبيق للشركة
                        $company->notify(new PaymentNotification([
                            'type'     => 'offer',
                            'offer_id' => $offer->id,
                            'mgs'      => [
                                'en' => 'You have a new offer with: ' . $offerPrice . ' CHF',
                                'de' => 'Sie haben ein neues Angebot mit: ' . $offerPrice . ' CHF',
                                'it' => 'Hai una nuova offerta con: ' . $offerPrice . ' CHF',
                                'fr' => 'Vous avez une nouvelle offre avec: ' . $offerPrice . ' CHF',
                            ],
                            'serves'   => optional($offer->type)->getTranslations('name'),
                        ]));

                        // إشعار داخلي للأدمنز
                        $admins = User::query()
                            ->where('role', 'admin')
                            ->where('available_notification', '1')
                            ->get();
                        HelperFunc::sendMultilangNotification($admins, 'offer_purchased', $offer->id, [
                            'en' => 'The offer "' . $offer->name . '" has been purchased by the company "' . $company->name . '" for ' . $offerPrice . ' CHF.',
                            'de' => 'Das Angebot "' . $offer->name . '" wurde von der Firma "' . $company->name . '" für ' . $offerPrice . ' CHF gekauft.',
                        ]);

                        // إيميلات الشراء
                        Mail::to($company->email)->send(new OfferPurchasedCompany($offer, $company, $offerPrice));
                        if ($adminEmail) {
                            Mail::to($adminEmail)->send(new OfferPurchasedAdmin($offer, $company, $offerPrice));
                        }

                        $purchasedCompanyIds[] = $company->id;
                    } catch (\Exception $e) {
                        Log::error('Auto-buy failed for company', [
                            'company_id' => $company->id,
                            'offer_id'   => $offer->id,
                            'error'      => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
            }

            // 3) الشركات الباقية (مش مفعّلة شراء تلقائي، أو رصيدها مش كافي، أو الأدمن أوقف الشراء التلقائي)
            //    تاخد إيميل وجود أوفر جديد في الشوب — بشرط إن لسة فيه count
            if ($offer->count > 0) {
                $remainingCompanies = $matchingCompanies->whereNotIn('id', $purchasedCompanyIds);

                foreach ($remainingCompanies as $company) {
                    try {
                        Mail::to($company->email)->send(new NewOfferForCompany($offer, $company, $offerPrice));
                    } catch (\Exception $e) {
                        Log::error('Error sending NewOfferForCompany', [
                            'company_id' => $company->id,
                            'offer_id'   => $offer->id,
                            'error'      => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in processOfferDistribution', [
                'offer_id' => $offer->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
