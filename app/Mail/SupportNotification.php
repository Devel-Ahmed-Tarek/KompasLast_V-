<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class SupportNotification extends Mailable
{
    public $complaint;
    public $action;
    public $locale;

    public function __construct($complaint, $action, ?string $locale = null)
    {
        $this->complaint = $complaint;
        $this->action    = $action;
        $this->locale    = $locale ?? 'en';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        $subjectKey = $this->action === 'approved'
            ? 'support_notification.subject_approved'
            : 'support_notification.subject_rejected';

        return $this->view('email.support_notification')
            ->subject(__($subjectKey))
            ->with([
                'complaint' => $this->complaint,
                'action'    => $this->action,
            ]);
    }
}
