<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupervisorOfferConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public Offer $offer;
    public bool $distributionRan;

    public function __construct(Offer $offer, bool $distributionRan)
    {
        $this->offer           = $offer;
        $this->distributionRan = $distributionRan;
    }

    public function build()
    {
        app()->setLocale('de');

        return $this->view('email.supervisor_offer_confirmed')
            ->subject(__('supervisor_offer_confirmed.subject', ['id' => $this->offer->id]));
    }
}
