<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupervisorAutoPurchases extends Mailable
{
    use Queueable, SerializesModels;

    public Offer $offer;

    /** @var array<int, array{company: mixed, price: float}> */
    public array $purchases;

    public function __construct(Offer $offer, array $purchases)
    {
        $this->offer     = $offer;
        $this->purchases = $purchases;
    }

    public function build()
    {
        app()->setLocale('de');

        return $this->view('email.supervisor_auto_purchases')
            ->subject(__('supervisor_auto_purchases.subject', ['id' => $this->offer->id]));
    }
}
