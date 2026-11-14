<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferEstimatedQuote;
use App\Services\OfferChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyEstimatedQuoteController extends Controller
{
    public function __construct(private OfferChatService $chat)
    {
    }

    public function show(int $offerId)
    {
        $company = Auth::user();
        $offer   = Offer::find($offerId);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found', []);
        }

        try {
            $this->chat->assertCompanyPurchased($offer, $company->id);
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(403, 'Forbidden', $e->errors()['company_id'] ?? ['Not purchased']);
        }

        $quote = OfferEstimatedQuote::where('offer_id', $offerId)
            ->where('company_id', $company->id)
            ->latest('id')
            ->first();

        return HelperFunc::sendResponse(200, 'OK', [
            'quote' => $quote?->toApiArray(),
        ]);
    }

    public function store(Request $request, ?int $offerId = null)
    {
        $offerId = $offerId ?: (int) $request->input('offer_id');

        $validator = Validator::make(array_merge($request->all(), ['offer_id' => $offerId]), [
            'offer_id'    => 'required|integer|exists:offers,id',
            'mode'        => 'required|in:single,range',
            'amount'      => 'nullable|numeric|gt:0',
            'amount_from' => 'nullable|numeric|gt:0',
            'amount_to'   => 'nullable|numeric|gt:0',
            'note'        => 'nullable|string|max:2000',
            'currency'    => 'nullable|string|max:8',
        ]);

        $validator->after(function ($v) use ($request) {
            $mode = $request->input('mode');
            if ($mode === 'single' && ! $request->filled('amount')) {
                $v->errors()->add('amount', 'amount is required for single mode');
            }
            if ($mode === 'range') {
                if (! $request->filled('amount_from') || ! $request->filled('amount_to')) {
                    $v->errors()->add('amount_from', 'amount_from and amount_to are required for range mode');
                } elseif ((float) $request->input('amount_to') < (float) $request->input('amount_from')) {
                    $v->errors()->add('amount_to', 'amount_to must be >= amount_from');
                }
            }
        });

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $company = Auth::user();
        $offer   = Offer::find($offerId);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found', []);
        }

        try {
            $this->chat->assertCompanyPurchased($offer, $company->id);
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(403, 'Forbidden', $e->errors()['company_id'] ?? ['Not purchased']);
        }

        $todayCount = OfferEstimatedQuote::where('company_id', $company->id)
            ->where('offer_id', $offer->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount >= 5) {
            return HelperFunc::sendResponse(429, 'Too many quotes today', [
                'Max 5 estimated quotes per offer per day.',
            ]);
        }

        $quote = OfferEstimatedQuote::create([
            'offer_id'    => $offer->id,
            'company_id'  => $company->id,
            'mode'        => $request->input('mode'),
            'amount'      => $request->input('mode') === 'single' ? $request->input('amount') : null,
            'amount_from' => $request->input('mode') === 'range' ? $request->input('amount_from') : null,
            'amount_to'   => $request->input('mode') === 'range' ? $request->input('amount_to') : null,
            'currency'    => strtoupper($request->input('currency', 'EUR')),
            'note'        => $request->input('note'),
        ]);

        $conversation = null;
        $message      = null;

        try {
            $conversation = $this->chat->findOrCreate($offer, $company->id);
            $message      = $this->chat->send(
                $conversation,
                'company',
                $company->id,
                $quote->formatMessageBody()
            );
            $quote->conversation_id = $conversation->id;
            $quote->message_id      = $message->id;
            $quote->save();
        } catch (\Throwable $e) {
            // Quote is saved even if chat fails.
        }

        if ($offer->email) {
            try {
                Mail::raw(
                    $quote->formatMessageBody() . "\n\n— " . ($company->name ?: 'AuftragKompass Partner'),
                    function ($mail) use ($offer, $company) {
                        $mail->to($offer->email)
                            ->subject('Kostenschätzung (unverbindlich) — AuftragKompass');
                        if ($company->email) {
                            $mail->replyTo($company->email, $company->name);
                        }
                    }
                );
            } catch (\Throwable $e) {
                // ignore mail failures
            }
        }

        return HelperFunc::sendResponse(201, 'Created', [
            'quote'            => $quote->toApiArray(),
            'conversation_id'  => $conversation?->id,
            'message'          => $message ? $this->chat->formatMessage($message) : null,
        ]);
    }
}
