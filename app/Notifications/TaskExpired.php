<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 10;
    public $timeout = 60;

    public $domain;
    public $task;

    public function __construct($domain, $task)
    {
        $this->domain = $domain;
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans($this->domain . '::notifications.taskExpired.subject', ['name' => $this->task->name]))
            ->greeting(trans($this->domain . '::notifications.taskExpired.greeting', ['name' => $notifiable->name]))
            ->line(trans($this->domain . '::notifications.taskExpired.intro'))
            ->action(trans($this->domain . '::notifications.taskExpired.action'), secure_url('tasks/' . $this->task->id));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
