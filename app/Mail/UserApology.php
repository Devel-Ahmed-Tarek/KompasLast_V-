<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class UserApology extends Mailable
{
    public $complaint;

    public function __construct($complaint)
    {
        $this->complaint = $complaint;
    }

    public function build()
    {
        return $this->view('email.user_apology')
            ->subject('Apology for Your Complaint')
            ->with([
                'complaint' => $this->complaint,
            ]);
    }
}
