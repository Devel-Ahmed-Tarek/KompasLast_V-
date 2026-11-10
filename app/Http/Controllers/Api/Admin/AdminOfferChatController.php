<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferConversation;
use App\Models\OfferMessage;
use App\Models\Shopping_list;
use App\Models\User;
use App\Services\OfferChatService;
use Illuminate\Http\Request;

class AdminOfferChatController extends Controller
{
    public function __construct(private OfferChatService $chat)
    {
    }

    /**
     * Global inbox: all offer chats across companies.
     */
    public function index(Request $request)
    {
        $limit = min(100, max(1, (int) $request->get('per_page', 20)));

        $query = OfferConversation::with([
            'offer:id,name,email,phone,type_id,city,ort,zipcode,adresse,date,lang',
            'offer.type',
            'company:id,name,email,phone,img',
            'latestMessage',
        ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($request->filled('offer_id')) {
            $query->where('offer_id', (int) $request->offer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', (int) $request->company_id);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($inner) use ($q) {
                $inner->whereHas('offer', function ($o) use ($q) {
                    $o->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('id', $q);
                })->orWhereHas('company', function ($c) use ($q) {
                    $c->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            });
        }

        $paginator = $query->paginate($limit);

        $rows = collect($paginator->items())->map(function (OfferConversation $conversation) {
            return $this->formatAdminConversation($conversation);
        })->values();

        return HelperFunc::pagination($paginator, $rows);
    }

    /**
     * All company chats for one offer (purchasers + existing threads).
     */
    public function byOffer(int $offerId)
    {
        $offer = Offer::with('type')->find($offerId);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        $companyIds = Shopping_list::where('offer_id', $offer->id)
            ->pluck('user_id')
            ->unique()
            ->values();

        $conversations = OfferConversation::with(['company:id,name,email,phone,img', 'latestMessage'])
            ->where('offer_id', $offer->id)
            ->get()
            ->keyBy('company_id');

        $rows = $companyIds->map(function ($companyId) use ($offer, $conversations) {
            $conversation = $conversations->get($companyId);
            $company = $conversation?->company ?: User::select('id', 'name', 'email', 'phone', 'img')->find($companyId);
            if (! $company) {
                return null;
            }

            $messagesCount = $conversation
                ? OfferMessage::where('conversation_id', $conversation->id)->count()
                : 0;

            return [
                'conversation_id' => $conversation?->id,
                'has_messages'    => $messagesCount > 0,
                'messages_count'  => $messagesCount,
                'company'         => [
                    'id'    => $company->id,
                    'name'  => $company->name,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'img'   => $company->img ? url($company->img) : null,
                ],
                'last_message'    => $conversation?->latestMessage ? [
                    'id'          => $conversation->latestMessage->id,
                    'body'        => $conversation->latestMessage->body,
                    'sender_type' => $conversation->latestMessage->sender_type,
                    'created_at'  => optional($conversation->latestMessage->created_at)->toIso8601String(),
                ] : null,
                'last_message_at' => optional($conversation?->last_message_at)->toIso8601String(),
            ];
        })->filter()->values();

        return HelperFunc::sendResponse(200, 'OK', [
            'offer'         => $this->formatOfferBrief($offer),
            'conversations' => $rows,
            'total'         => $rows->count(),
        ]);
    }

    /**
     * Messages between customer and one company for an offer.
     */
    public function byOfferCompany(int $offerId, int $companyId, Request $request)
    {
        $offer = Offer::with('type')->find($offerId);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        $bought = Shopping_list::where('offer_id', $offerId)
            ->where('user_id', $companyId)
            ->exists();

        if (! $bought) {
            return HelperFunc::sendResponse(404, 'Company did not purchase this offer');
        }

        $company = User::select('id', 'name', 'email', 'phone', 'img')->find($companyId);
        if (! $company) {
            return HelperFunc::sendResponse(404, 'Company not found');
        }

        $conversation = OfferConversation::with('latestMessage')
            ->where('offer_id', $offerId)
            ->where('company_id', $companyId)
            ->first();

        $afterId = (int) $request->query('after_id', 0);
        $messages = $conversation
            ? $this->chat->messages($conversation, $afterId)
            : collect();

        return HelperFunc::sendResponse(200, 'OK', [
            'offer'           => $this->formatOfferBrief($offer),
            'company'         => [
                'id'    => $company->id,
                'name'  => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'img'   => $company->img ? url($company->img) : null,
            ],
            'conversation_id' => $conversation?->id,
            'messages'        => $messages,
            'messages_count'  => $conversation
                ? OfferMessage::where('conversation_id', $conversation->id)->count()
                : 0,
        ]);
    }

    /**
     * Open a conversation thread by conversation id.
     */
    public function show(int $conversationId, Request $request)
    {
        $conversation = OfferConversation::with([
            'offer.type',
            'company:id,name,email,phone,img',
            'latestMessage',
        ])->find($conversationId);

        if (! $conversation) {
            return HelperFunc::sendResponse(404, 'Chat not found');
        }

        $afterId = (int) $request->query('after_id', 0);

        return HelperFunc::sendResponse(200, 'OK', [
            'conversation' => $this->formatAdminConversation($conversation),
            'messages'     => $this->chat->messages($conversation, $afterId),
        ]);
    }

    private function formatAdminConversation(OfferConversation $conversation): array
    {
        $base = $this->chat->formatCompanyConversation($conversation);

        return array_merge($base, [
            'company' => [
                'id'    => $conversation->company?->id,
                'name'  => $conversation->company?->name,
                'email' => $conversation->company?->email,
                'phone' => $conversation->company?->phone,
                'img'   => $conversation->company?->img ? url($conversation->company->img) : null,
            ],
            'messages_count' => OfferMessage::where('conversation_id', $conversation->id)->count(),
        ]);
    }

    private function formatOfferBrief(Offer $offer): array
    {
        $service = null;
        if ($offer->type) {
            $lang = $offer->lang ?: 'de';
            $service = method_exists($offer->type, 'getTranslation')
                ? ($offer->type->getTranslation('name', $lang, false) ?: $offer->type->name)
                : $offer->type->name;
            if (is_array($service)) {
                $service = $service[$lang] ?? $service['de'] ?? reset($service) ?: null;
            }
        }

        return [
            'id'      => $offer->id,
            'name'    => $offer->name,
            'email'   => $offer->email,
            'phone'   => $offer->phone,
            'service' => $service,
            'adresse' => $offer->adresse,
            'zipcode' => $offer->zipcode,
            'ort'     => $offer->ort ?: $offer->city,
            'date'    => $offer->date,
            'lang'    => $offer->lang,
        ];
    }
}
