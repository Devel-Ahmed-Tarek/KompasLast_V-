<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOfferForCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $company;
    public $price;

    /**
     * @param  mixed  $offer
     * @param  mixed  $company
     * @param  float|null  $price
     */
    public function __construct($offer, $company, ?float $price = null)
    {
        $this->offer  = $offer;
        $this->company = $company;
        $this->price   = $price;
    }

    public function build()
    {
        return $this->view('email.NewOfferForCompany')
            ->subject('Neues Angebot in Ihrem AuftragKompass-Konto');
    }
}

