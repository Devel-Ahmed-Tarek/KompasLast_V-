<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNewOffer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \App\Models\Offer
     */
    public $offer;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New customer offer created')
            ->view('email.admin_new_offer');
    }
}

