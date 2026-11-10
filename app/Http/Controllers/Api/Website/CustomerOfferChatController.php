<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferConversation;
use App\Services\OfferChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerOfferChatController extends Controller
{
    public function __construct(private OfferChatService $chat)
    {
    }

    public function index(string $token)
    {
        $offer = $this->findOffer($token);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        return HelperFunc::sendResponse(200, 'OK', [
            'conversations' => $this->chat->listForOffer($offer),
        ]);
    }

    public function show(string $token, int $companyId, Request $request)
    {
        $offer = $this->findOffer($token);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        try {
            $conversation = $this->chat->findOrCreate($offer, $companyId);
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(403, 'Unternehmen gehört nicht zu dieser Anfrage', $e->errors());
        }

        $this->chat->markRead($conversation, 'customer');

        $afterId = (int) $request->query('after_id', 0);

        return HelperFunc::sendResponse(200, 'OK', [
            'conversation_id' => $conversation->id,
            'company_id'      => $companyId,
            'messages'        => $this->chat->messages($conversation, $afterId),
        ]);
    }

    public function store(string $token, int $companyId, Request $request)
    {
        $offer = $this->findOffer($token);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        try {
            $conversation = $this->chat->findOrCreate($offer, $companyId);
            $message = $this->chat->send($conversation, 'customer', null, $request->input('body'));
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(403, 'Nachricht konnte nicht gesendet werden', $e->errors());
        }

        return HelperFunc::sendResponse(201, 'Gesendet', $this->chat->formatMessage($message));
    }

    private function findOffer(string $token): ?Offer
    {
        return Offer::where(function ($q) use ($token) {
            $q->where('track_token', $token)
                ->orWhere('confirm_token', $token);
        })->first();
    }
}
