<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Offer;
use App\Models\OfferExecution;
use App\Models\ReviewCompany;
use App\Models\Shopping_list;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferExecutionController extends Controller
{

    public function getCompanysByOffer($offer_id)
    {
        if (! Shopping_list::where('offer_id', $offer_id)->exists()) {
            return HelperFunc::sendResponse(422, "offer not found");
        }

        $companys = Shopping_list::where('offer_id', $offer_id)
            ->with(['company'])
            ->get()
            ->pluck('company')
            ->unique('id');

        return HelperFunc::sendResponse(200, "done", $companys);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'stars'   => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $review = ReviewCompany::create($request->all());

        $company = User::find($request->user_id);

        if ($company) {
            $data = [
                'type'      => 'review',
                'review_id' => $review->id,
                'mgs'       => [
                    'en' => 'You have received a new review.',
                    'de' => 'Sie haben eine neue Bewertung erhalten.',
                    'it' => 'Hai ricevuto una nuova recensione.',
                    'fr' => 'Vous avez reçu un nouvel avis.',
                ],
                'reviewer'  => $review->name,
                'stars'     => $review->stars,
            ];

            $company->notify(new PaymentNotification($data));
        }

        return HelperFunc::sendResponse(201, 'Review added successfully', $review);
    }

    public function storeExecution(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id'  => 'nullable|exists:users,id',
            'offer_id'    => 'required|exists:offers,id',
            'is_executed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $ex = OfferExecution::updateOrCreate(
            [
                'company_id' => $request->input('company_id'),
                'offer_id'   => $request->input('offer_id'),
            ],
            [
                'is_executed' => $request->input('is_executed'),
            ]
        );

        return HelperFunc::sendResponse(201, 'Offer execution added successfully', $ex);
    }

    public function getInfoOffer($offer_id)
    {
        $offer = Offer::select(["name", "email"])->find($offer_id);
        if (! $offer) {
            return HelperFunc::sendResponse(404, "Offer not found");
        }
        return HelperFunc::sendResponse(200, "done", $offer);
    }

    // store Announcement
    public function storeAnnouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment'  => 'nullable|string',
            'offer_id' => 'required|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $announcement = Announcement::create($request->all());

        return HelperFunc::sendResponse(201, 'Announcement added successfully', $announcement);
    }
}
