<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferPurchasedCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $company;
    public $price;

    /**
     * @param  mixed  $offer
     * @param  mixed  $company
     * @param  float  $price
     */
    public function __construct($offer, $company, $price)
    {
        $this->offer   = $offer;
        $this->company = $company;
        $this->price   = $price;
    }

    public function build()
    {
        return $this->view('email.OfferPurchasedCompany')
            ->subject('Glückwunsch! Sie haben einen neuen Auftrag erworben');
    }
}

