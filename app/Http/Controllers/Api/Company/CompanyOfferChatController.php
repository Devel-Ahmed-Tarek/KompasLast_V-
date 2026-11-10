<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferConversation;
use App\Models\Shopping_list;
use App\Services\OfferChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyOfferChatController extends Controller
{
    public function __construct(private OfferChatService $chat)
    {
    }

    /**
     * List all chats for the authenticated company.
     */
    public function index()
    {
        $company = Auth::user();

        return HelperFunc::sendResponse(200, 'OK', [
            'conversations' => $this->chat->listForCompany($company),
            'unread_total'  => $this->chat->unreadTotalForCompany($company),
        ]);
    }

    /**
     * Unread badge count for sidebar/header.
     */
    public function unread()
    {
        $company = Auth::user();

        return HelperFunc::sendResponse(200, 'OK', [
            'unread_total' => $this->chat->unreadTotalForCompany($company),
        ]);
    }

    /**
     * Open (or create) a chat for an offer the company purchased.
     * Useful from "My offers" detail screen.
     */
    public function open(Request $request)
    {
        $company = Auth::user();

        $validator = Validator::make($request->all(), [
            'offer_id' => 'required|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $offer = Offer::findOrFail($request->offer_id);

        try {
            $conversation = $this->chat->findOrCreate($offer, $company->id);
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(403, 'Sie haben dieses Angebot nicht gekauft', $e->errors());
        }

        $this->chat->markRead($conversation, 'company');

        return HelperFunc::sendResponse(200, 'OK', [
            'conversation' => $this->chat->formatCompanyConversation($conversation->fresh(['offer.type', 'latestMessage'])),
            'messages'     => $this->chat->messages($conversation),
        ]);
    }

    /**
     * Get messages for a conversation.
     * Optional query: after_id for polling new messages only.
     */
    public function show(int $conversationId, Request $request)
    {
        $company = Auth::user();
        $conversation = OfferConversation::with(['offer.type', 'latestMessage'])->find($conversationId);

        if (! $conversation || (int) $conversation->company_id !== (int) $company->id) {
            return HelperFunc::sendResponse(404, 'Chat nicht gefunden');
        }

        $afterId = (int) $request->query('after_id', 0);

        // Only mark as read when loading full history (not silent poll).
        if ($afterId <= 0) {
            $this->chat->markRead($conversation, 'company');
            $conversation->refresh();
        }

        return HelperFunc::sendResponse(200, 'OK', [
            'conversation' => $this->chat->formatCompanyConversation($conversation),
            'messages'     => $this->chat->messages($conversation, $afterId),
        ]);
    }

    /**
     * Send a message as company.
     */
    public function store(int $conversationId, Request $request)
    {
        $company = Auth::user();
        $conversation = OfferConversation::with(['offer.type', 'latestMessage'])->find($conversationId);

        if (! $conversation || (int) $conversation->company_id !== (int) $company->id) {
            return HelperFunc::sendResponse(404, 'Chat nicht gefunden');
        }

        $stillOwned = Shopping_list::where('offer_id', $conversation->offer_id)
            ->where('user_id', $company->id)
            ->exists();
        if (! $stillOwned) {
            return HelperFunc::sendResponse(403, 'Sie haben dieses Angebot nicht gekauft');
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        try {
            $message = $this->chat->send($conversation, 'company', $company->id, $request->input('body'));
        } catch (ValidationException $e) {
            return HelperFunc::sendResponse(422, 'Nachricht konnte nicht gesendet werden', $e->errors());
        }

        return HelperFunc::sendResponse(201, 'Gesendet', [
            'message'      => $this->chat->formatMessage($message),
            'conversation' => $this->chat->formatCompanyConversation($conversation->fresh(['offer.type', 'latestMessage'])),
        ]);
    }
}
