<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    public $tries = 10;
    public $timeout = 60;

    public $domain;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($domain, $token)
    {
        $this->domain = $domain;
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans($this->domain . '::notifications.resetEmail.subject'))
            ->greeting(trans($this->domain . '::notifications.resetEmail.greeting', ['name' => $notifiable->name]))
            ->line(trans($this->domain . '::notifications.resetEmail.intro'))
            ->action(trans($this->domain . '::notifications.resetEmail.action'), route($this->domain . '.password.reset.token', ['token' => $this->token]))
            ->line(trans($this->domain . '::notifications.resetEmail.outro'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
