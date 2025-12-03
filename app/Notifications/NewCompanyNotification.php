<?php
namespace App\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewCompanyNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $title;
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param array $data
     * @param string $message
     * @param string $title
     */
    public function __construct($data, $message = 'New company registered', $title = 'Company Registration')
    {
        $this->data    = $data;
        $this->title   = $title;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'company_id' => $this->data['company_id'],
            'title'      => $this->title,
            'message'    => $this->message,
            'url'        => '/admin/companies/' . $this->data['company_id'], // Example URL
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title'      => $this->title,
            'message'    => $this->message,
            'company_id' => $this->data['company_id'],
        ]);
    }

    /**
     * Specify the broadcast channel.
     *
     * @return Channel|string
     */
    public function broadcastOn()
    {
        return new Channel('notification.' . $notifiable->id);
    }

    /**
     * Specify the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'new-company-notification';
    }
}