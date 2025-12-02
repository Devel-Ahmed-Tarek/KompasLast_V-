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

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    public function build()
    {
        return $this->subject('Reminder: Offer Execution Date Passed')
            ->view('emails.offer_reminder')
            ->with([
                'offer' => $this->offer,
            ]);
    }
}
