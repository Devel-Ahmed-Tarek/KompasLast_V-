<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferEstimatedQuote;
use Illuminate\Http\Request;

class AdminEstimatedQuoteController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(100, max(1, (int) $request->get('per_page', 20)));

        $query = OfferEstimatedQuote::with([
            'offer:id,name,email,phone,type_id',
            'company:id,name,email,phone,img',
        ])->orderByDesc('id');

        if ($request->filled('offer_id')) {
            $query->where('offer_id', (int) $request->offer_id);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', (int) $request->company_id);
        }

        $page = $query->paginate($limit);

        $rows = collect($page->items())->map(function (OfferEstimatedQuote $quote) {
            return array_merge($quote->toApiArray(), [
                'offer' => [
                    'id'    => $quote->offer?->id,
                    'name'  => $quote->offer?->name,
                    'email' => $quote->offer?->email,
                ],
                'company' => [
                    'id'    => $quote->company?->id,
                    'name'  => $quote->company?->name,
                    'email' => $quote->company?->email,
                    'img'   => $quote->company?->img ? url($quote->company->img) : null,
                ],
                'conversation_id' => $quote->conversation_id,
                'message_id'      => $quote->message_id,
            ]);
        })->values();

        return HelperFunc::pagination($page, $rows);
    }

    public function byOffer(int $offerId)
    {
        $offer = Offer::find($offerId);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found', []);
        }

        $quotes = OfferEstimatedQuote::with(['company:id,name,email,img'])
            ->where('offer_id', $offerId)
            ->orderByDesc('id')
            ->get()
            ->map(function (OfferEstimatedQuote $quote) {
                return array_merge($quote->toApiArray(), [
                    'company' => [
                        'id'    => $quote->company?->id,
                        'name'  => $quote->company?->name,
                        'email' => $quote->company?->email,
                        'img'   => $quote->company?->img ? url($quote->company->img) : null,
                    ],
                    'conversation_id' => $quote->conversation_id,
                    'message_id'      => $quote->message_id,
                ]);
            })
            ->values();

        return HelperFunc::sendResponse(200, 'OK', [
            'offer_id' => $offerId,
            'quotes'   => $quotes,
            'total'    => $quotes->count(),
        ]);
    }
}
