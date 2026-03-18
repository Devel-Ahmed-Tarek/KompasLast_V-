<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class UserApology extends Mailable
{
    public $complaint;
    public $locale;

    public function __construct($complaint, ?string $locale = null)
    {
        $this->complaint = $complaint;
        $this->locale    = $locale ?? ($complaint->lang ?? null) ?? 'en';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->view('email.user_apology')
            ->subject(__('user_apology.subject'))
            ->with([
                'complaint' => $this->complaint,
            ]);
    }
}
