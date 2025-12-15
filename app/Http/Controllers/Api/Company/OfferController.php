<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferShopResource;
use App\Http\Resources\OfferWithAnswersResource;
use App\Models\ConfigApp;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\OfferExecution;
use App\Models\Shopping_list;
use App\Models\Type;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        // Check if the shop is active
        $config = ConfigApp::first();
        if (! $config || $config->on_shop == 1) {
            return HelperFunc::apiResponse(true, 200, ['message' => 'Shop Is Stoping']);
        }

        // Get the currently logged-in company
        $currentCompany   = Auth::user()->load(['companyDetails', 'typesComapny']);
        $currentCompanyId = $currentCompany->id;

        $admins = User::where("role", "admin")->where('available_notification', '1')->get();
        Notification::send($admins, new \App\Notifications\PaymentNotification([
            'type'    => "oppenedShop",
            'type_id' => $currentCompany->id,
            'mgs'     =>
            [
                'en' => 'The company ' . $currentCompany->name . ' Oppened The Shop',
                'de' => 'Die Firma ' . $currentCompany->name . ' hat den Shop geoeffnet',
            ],
        ]));

        // Query offers that are not yet purchased by the logged-in company
        $query = Offer::where('status', 1)
            ->where('count', '>', 0)
            ->where('date', '>', now())
            ->whereHas('type', function ($query) use ($currentCompany) {
                // Check if the offer type is related to the current company
                $query->whereIn('id', $currentCompany->typesComapny->pluck('id'));
            })
            ->whereDoesntHave('Shopping_list', function ($query) use ($currentCompanyId) {
                // Ensure the offer is not in the shopping list of the current company
                $query->where('user_id', $currentCompanyId);
            })
            ->with([
                'type',
                'answers' => function ($query) {
                    // تحميل فقط الإجابات للأسئلة التي show_before_purchase = true
                    $query->whereHas('question', function ($q) {
                        $q->where('show_before_purchase', true);
                    });
                },
                'answers.question',
                'answers.options',
                'answers.files'
            ]);

        // Apply search functionality if the search parameter is provided
        if ($request->has('search')) {
            $search = $request->query('search');

            // Search in offer's name and offer type's name
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('type', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Order offers by ID and paginate results
        $offers = $query->orderBy('id', 'desc')->paginate(10);

        // Return paginated response using HelperFunc
        return HelperFunc::pagination($offers, OfferShopResource::collection($offers));
    }

    public function byOffer(Request $request)
    {
        if (auth()->user()->companyDetails->sucsses == 0) {
            return HelperFunc::sendResponse(403, "Data is not available. Update files and contracts to be approved.", []);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'offer_id'  => 'required|exists:offers,id',
            'user_id'   => 'required|exists:users,id',
            'coupon_id' => 'nullable|exists:coupons,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, "Validation Error", [
                'errors' => $validator->errors(),
            ]);
        }

        // Get coupon if exists
        $coupon = null;
        if ($request->filled('coupon_id')) {
            $coupon = Coupon::where('id', $request->coupon_id)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('usage_limit')->orWhereColumn('used', '<', 'usage_limit');
                })
                ->first();

            if (! $coupon) {
                return HelperFunc::sendResponse(404, "Coupon not found or not valid", []);
            }

            if (! $coupon->multi_used) {
                $usedBefore = DB::table('company_coupons')
                    ->where('company_id', auth()->user()->companyDetails->id)
                    ->where('coupon_id', $coupon->id)
                    ->exists();

                if ($usedBefore) {
                    return HelperFunc::sendResponse(400, "You have already used this coupon", []);
                }
            }
        }

        // Start transaction
        DB::beginTransaction();
        try {
            $offer = Offer::find($request->offer_id);

            if (! $offer || $offer->status == 0) {
                return HelperFunc::sendResponse(403, "Offer is not available", []);
            }

            if ($offer->count <= 0) {
                return HelperFunc::sendResponse(403, "Offer is sold out", []);
            }

            $user = User::where('role', 'company')->find($request->user_id);
            if (! $user) {
                return HelperFunc::sendResponse(404, "User not found", []);
            }

            $amountTotal  = $user->wallet->amount;
            $expenseTotal = $user->wallet->expense;
            $walletTotal  = $amountTotal - $expenseTotal;

            $typeOffer = $offer->type;
            if (! $typeOffer) {
                return HelperFunc::sendResponse(404, "Offer type not found", []);
            }

            $offerPrice = $typeOffer->price / $offer->Number_of_offers;

            if ($offerPrice > $walletTotal) {
                return HelperFunc::sendResponse(403, "Insufficient wallet balance. Please add funds.", []);
            }

            // Add to shopping list
            Shopping_list::create([
                'offer_id' => $offer->id,
                'user_id'  => $user->id,
                'type'     => 'S',
            ]);

            // Decrease offer count
            $offer->decrement('count');

            // Apply coupon if exists
            if ($coupon) {
                if ($coupon->type == 'percentage') {
                    $discount = $offerPrice * ($coupon->discount / 100);
                } else {
                    $discount = $coupon->discount;
                }

                // Don't allow discount to exceed the price
                $discount = min($discount, $offerPrice);

                // Update total expense
                $expenseTotal += $offerPrice - $discount;

                // Save coupon usage
                DB::table('company_coupons')->insert([
                    'company_id' => $user->id,
                    'coupon_id'  => $coupon->id,
                    'offer_id'   => $offer->id,
                    'status'     => 'used'
                ]);
            } else {
                $expenseTotal += $offerPrice;
            }

            // Update wallet expense
            $user->wallet()->updateOrCreate([], ['expense' => $expenseTotal]);

            // send notification to Admin
            $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();

            HelperFunc::sendMultilangNotification($admins, "offer_purchased", $offer->id, [
                'en' => 'The offer "' . $offer->name . '" has been purchased by the company: ' . $user->name . '.' . "\n" . 'The offer price is: ' . $offerPrice . ' CHF',
                'de' => 'Das Angebot "' . $offer->name . '" wurde von der Firma ' . $user->name . ' gekauft. ' . "\n" . 'Der Angebotspreis ist: ' . $offerPrice . ' CHF',
            ]);
            DB::commit();
            return HelperFunc::sendResponse(201, __('messages.offer_purchased_successfully'), []);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction failed: ' . $e->getMessage());
            return HelperFunc::sendResponse(500, __('messages.transaction_error'), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getCouponByCode(Request $request)
    {
        $code      = $request->input('code');
        $companyId = Auth::user()->id;
        if (empty($code) || empty($companyId)) {
            return HelperFunc::sendResponse(400, 'Coupon code and company ID are required.');
        }
        if (! is_numeric($companyId)) {
            return HelperFunc::sendResponse(400, 'Invalid company ID.');
        }
        if (strlen($code) < 3 || strlen($code) > 50) {
            return HelperFunc::sendResponse(400, 'Coupon code must be between 3 and 50 characters.');
        }
        // Check if the company exists
        $companyExists = User::where('id', $companyId)->exists();
        if (! $companyExists) {
            return HelperFunc::sendResponse(200, 'Company not found.');
        }

        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereColumn('used', '<', 'usage_limit');
            })
            ->first();

        if (! $coupon) {
            return HelperFunc::sendResponse(404, 'Coupon not found or not valid.');
        }

        if (! $coupon->multi_used) {
            $usedBefore = DB::table('company_coupons')
                ->where('company_id', $companyId)
                ->where('coupon_id', $coupon->id)
                ->exists();

            if ($usedBefore) {
                return HelperFunc::sendResponse(400, 'You have already used this coupon.');
            }
        }

        return HelperFunc::sendResponse(200, 'Coupon details retrieved successfully.', $coupon);
    }
    public function getMyOffer(Request $request)
    {
        // تحقق من وجود مستخدم مسجل دخول
        if (! auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // استعلام العروض الخاصة بالمستخدم
        $query = Shopping_list::where('user_id', auth()->user()->id)
            ->with([
                'offer.type',
                'offer.answers.question',
                'offer.answers.options',
                'offer.answers.files',
                'fakeOffer'
            ]);

        if ($request->has('search')) {
            $search = $request->query('search');

            $query->where(function ($q) use ($search) {
                // البحث في الاسم، الهاتف، البريد الإلكتروني و النوع
                $q->whereHas('offer', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                    ->orWhereHas('offer.type', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // ترتيب العروض وعرضها بشكل مقسم
        $shoppingLists = $query->orderBy('id', 'desc')->paginate(10);

        // تحويل Shopping_list إلى Offers
        $offers = $shoppingLists->getCollection()->map(function ($shoppingItem) {
            return $shoppingItem->offer;
        });

        // استخدام Resource Collection
        $offersResource = OfferWithAnswersResource::collection($offers);

        return HelperFunc::pagination($shoppingLists, $offersResource);
    }

    public function singelOffer($offer_id)
    {
        if (! auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        $admins = User::where("role", "admin")->where('available_notification', '1')->get();

        $shoppingItem = Shopping_list::where('user_id', auth()->user()->id)
            ->where('offer_id', $offer_id)
            ->with([
                'offer.type',
                'offer.answers.question',
                'offer.answers.options',
                'offer.answers.files'
            ])
            ->first();

        if (! $shoppingItem || ! $shoppingItem->offer) {
            return HelperFunc::apiResponse(false, 204, ' Offer not found.');
        }

        Notification::send($admins, new \App\Notifications\PaymentNotification([
            'type'    => "companyOpenedOffer",
            'type_id' => $shoppingItem->id,
            'mgs'     => [
                'en' => 'The company :' . $user->name . ' oppened the offer :' . $shoppingItem->offer->name,
                "de" => "Die Firma: " . $user->name . " hat den Angebot: " . $shoppingItem->offer->name . " geoeffnet",
            ],
        ]));

        return new OfferWithAnswersResource($shoppingItem->offer);
    }

    public function getCompletedOffer()
    {
        $id     = Auth::user()->id;
        $offers = OfferExecution::with('offer')->where('company_id', $id)->where('is_executed', '1')->paginate(10);
        return HelperFunc::pagination($offers, $offers->items());
    }
}
