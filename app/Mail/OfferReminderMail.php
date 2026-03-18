<?php

// app/Mail/OfferReminderMail.php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $locale;

    public function __construct(Offer $offer, ?string $locale = null)
    {
        $this->offer  = $offer;
        // لغة العميل لو موجودة في الأوفر، أو براميتر، أو إنجليزي ديفولت
        $this->locale = $locale ?? ($offer->lang ?? null) ?? 'en';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject(__('offer_reminder.subject'))
            ->view('emails.offer_reminder')
            ->with([
                'offer' => $this->offer,
            ]);
    }
}
