<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class SupportNotification extends Mailable
{
    public $complaint;
    public $action;

    public function __construct($complaint, $action)
    {
        $this->complaint = $complaint;
        $this->action    = $action;
    }

    public function build()
    {
        $subject = $this->action === 'approved' ? 'Complaint Approved' : 'Complaint Rejected';
        return $this->view('email.support_notification')
            ->subject($subject)
            ->with([
                'complaint' => $this->complaint,
                'action'    => $this->action,
            ]);
    }
}
