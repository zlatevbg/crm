<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ViewingApproaching extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 10;
    public $timeout = 60;

    public $domain;
    public $viewing;

    public function __construct($domain, $viewing)
    {
        $this->domain = $domain;
        $this->viewing = $viewing;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans($this->domain . '::notifications.viewingApproaching.subject', ['id' => $this->viewing->id]))
            ->greeting(trans($this->domain . '::notifications.viewingApproaching.greeting', ['name' => $notifiable->name]))
            ->line(trans($this->domain . '::notifications.viewingApproaching.intro'))
            ->action(trans($this->domain . '::notifications.viewingApproaching.action'), secure_url('viewings'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
