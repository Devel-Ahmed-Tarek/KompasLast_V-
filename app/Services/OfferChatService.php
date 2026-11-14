<?php

namespace App\Services;

use App\Helpers\HelperFunc;
use App\Models\Offer;
use App\Models\OfferConversation;
use App\Models\OfferMessage;
use App\Models\Shopping_list;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OfferChatService
{
    public function assertCompanyPurchased(Offer $offer, int $companyId): void
    {
        $bought = Shopping_list::where('offer_id', $offer->id)
            ->where('user_id', $companyId)
            ->exists();

        if (! $bought) {
            throw ValidationException::withMessages([
                'company_id' => ['Company did not purchase this offer.'],
            ]);
        }
    }

    public function findOrCreate(Offer $offer, int $companyId): OfferConversation
    {
        $this->assertCompanyPurchased($offer, $companyId);

        return OfferConversation::firstOrCreate(
            [
                'offer_id'   => $offer->id,
                'company_id' => $companyId,
            ],
            [
                'last_message_at' => null,
            ]
        );
    }

    public function listForOffer(Offer $offer): Collection
    {
        $companyIds = Shopping_list::where('offer_id', $offer->id)->pluck('user_id')->unique()->values();

        return $companyIds->map(function ($companyId) use ($offer) {
            $conversation = OfferConversation::with(['company:id,name,img', 'latestMessage'])
                ->where('offer_id', $offer->id)
                ->where('company_id', $companyId)
                ->first();

            $company = $conversation?->company ?: User::select('id', 'name', 'img')->find($companyId);
            if (! $company) {
                return null;
            }

            $unread = 0;
            if ($conversation) {
                $unread = OfferMessage::where('conversation_id', $conversation->id)
                    ->where('sender_type', 'company')
                    ->when(
                        $conversation->customer_last_read_at,
                        fn ($q) => $q->where('created_at', '>', $conversation->customer_last_read_at),
                        fn ($q) => $q
                    )
                    ->count();
            }

            return [
                'conversation_id' => $conversation?->id,
                'company'         => [
                    'id'   => $company->id,
                    'name' => $company->name,
                    'img'  => $company->img ? url($company->img) : null,
                ],
                'last_message'    => $conversation?->latestMessage ? [
                    'id'          => $conversation->latestMessage->id,
                    'body'        => $conversation->latestMessage->body,
                    'sender_type' => $conversation->latestMessage->sender_type,
                    'created_at'  => optional($conversation->latestMessage->created_at)->toIso8601String(),
                ] : null,
                'unread_count'    => $unread,
                'last_message_at' => optional($conversation?->last_message_at)->toIso8601String(),
            ];
        })->filter()->values();
    }

    public function listForCompany(User $company): Collection
    {
        return OfferConversation::with([
            'offer:id,name,email,phone,type_id,city,ort,zipcode,adresse,date,lang',
            'latestMessage',
            'offer.type',
        ])
            ->where('company_id', $company->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (OfferConversation $conversation) => $this->formatCompanyConversation($conversation));
    }

    public function unreadTotalForCompany(User $company): int
    {
        $conversationIds = OfferConversation::where('company_id', $company->id)->pluck('id');
        if ($conversationIds->isEmpty()) {
            return 0;
        }

        return (int) OfferMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->count();
    }

    public function formatCompanyConversation(OfferConversation $conversation): array
    {
        $conversation->loadMissing(['offer.type', 'latestMessage']);

        $unread = OfferMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'customer')
            ->when(
                $conversation->company_last_read_at,
                fn ($q) => $q->where('created_at', '>', $conversation->company_last_read_at),
                fn ($q) => $q
            )
            ->count();

        $service = null;
        if ($conversation->offer?->type) {
            $lang = $conversation->offer->lang ?: 'de';
            $service = method_exists($conversation->offer->type, 'getTranslation')
                ? ($conversation->offer->type->getTranslation('name', $lang, false) ?: $conversation->offer->type->name)
                : $conversation->offer->type->name;
            if (is_array($service)) {
                $service = $service[$lang] ?? $service['de'] ?? reset($service) ?: null;
            }
        }

        return [
            'conversation_id' => $conversation->id,
            'offer'           => [
                'id'      => $conversation->offer?->id,
                'name'    => $conversation->offer?->name,
                'email'   => $conversation->offer?->email,
                'phone'   => $conversation->offer?->phone,
                'service' => $service,
                'adresse' => $conversation->offer?->adresse,
                'zipcode' => $conversation->offer?->zipcode,
                'ort'     => $conversation->offer?->ort ?: $conversation->offer?->city,
                'date'    => $conversation->offer?->date,
                'lang'    => $conversation->offer?->lang,
            ],
            'last_message'    => $conversation->latestMessage ? [
                'id'          => $conversation->latestMessage->id,
                'body'        => $conversation->latestMessage->body,
                'sender_type' => $conversation->latestMessage->sender_type,
                'created_at'  => optional($conversation->latestMessage->created_at)->toIso8601String(),
            ] : null,
            'unread_count'    => $unread,
            'last_message_at' => optional($conversation->last_message_at)->toIso8601String(),
        ];
    }

    public function messages(OfferConversation $conversation, int $afterId = 0): Collection
    {
        return OfferMessage::where('conversation_id', $conversation->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn (OfferMessage $m) => $this->formatMessage($m));
    }

    public function send(
        OfferConversation $conversation,
        string $senderType,
        ?int $senderId,
        string $body
    ): OfferMessage {
        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => ['Message body is required.'],
            ]);
        }

        if (mb_strlen($body) > 5000) {
            throw ValidationException::withMessages([
                'body' => ['Message is too long.'],
            ]);
        }

        $message = OfferMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => $senderType,
            'sender_id'       => $senderId,
            'body'            => $body,
        ]);

        $conversation->last_message_at = now();
        if ($senderType === 'customer') {
            $conversation->customer_last_read_at = now();
        } elseif ($senderType === 'company') {
            $conversation->company_last_read_at = now();
        }
        $conversation->save();

        $this->notifyCounterpart($conversation, $senderType, $body);

        return $message;
    }

    public function markRead(OfferConversation $conversation, string $readerType): void
    {
        if ($readerType === 'customer') {
            $conversation->customer_last_read_at = now();
            OfferMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'company')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            $conversation->company_last_read_at = now();
            OfferMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'customer')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
        $conversation->save();
    }

    public function formatMessage(OfferMessage $message): array
    {
        return [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'sender_id'   => $message->sender_id,
            'body'        => $message->body,
            'read_at'     => optional($message->read_at)->toIso8601String(),
            'created_at'  => optional($message->created_at)->toIso8601String(),
        ];
    }

    private function notifyCounterpart(OfferConversation $conversation, string $senderType, string $body): void
    {
        try {
            $conversation->loadMissing(['company', 'offer']);
            $preview = mb_substr($body, 0, 120);

            if ($senderType === 'customer' && $conversation->company) {
                HelperFunc::sendMultilangNotification(
                    collect([$conversation->company]),
                    'offer_chat_message',
                    $conversation->offer_id,
                    [
                        'en' => 'New customer message: ' . $preview,
                        'de' => 'Neue Kundennachricht: ' . $preview,
                        'ar' => 'رسالة جديدة من العميل: ' . $preview,
                        'fr' => 'Nouveau message client: ' . $preview,
                        'it' => 'Nuovo messaggio del cliente: ' . $preview,
                    ]
                );
            }

            if ($senderType === 'admin' && $conversation->company) {
                HelperFunc::sendMultilangNotification(
                    collect([$conversation->company]),
                    'offer_chat_message',
                    $conversation->offer_id,
                    [
                        'en' => 'New message from AuftragKompass: ' . $preview,
                        'de' => 'Neue Nachricht von AuftragKompass: ' . $preview,
                        'ar' => 'رسالة جديدة من AuftragKompass: ' . $preview,
                        'fr' => 'Nouveau message de AuftragKompass: ' . $preview,
                        'it' => 'Nuovo messaggio da AuftragKompass: ' . $preview,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Chat should not fail if notification fails.
        }
    }
}
