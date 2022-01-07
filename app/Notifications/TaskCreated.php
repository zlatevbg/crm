<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskCreated extends Notification
{
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
        $mailMessage = new MailMessage();

        $mailMessage->subject(trans($this->domain . '::notifications.taskCreated.subject', ['name' => $this->task->name]))
            ->greeting(trans($this->domain . '::notifications.taskCreated.greeting', ['name' => $notifiable->name]))
            ->line(trans($this->domain . '::notifications.taskCreated.intro', ['name' => $this->task->user->name]))
            ->action(trans($this->domain . '::notifications.taskCreated.action'), secure_url('tasks/' . $this->task->id));

        if ($this->task->end_at) {
            $mailMessage->line(trans($this->domain . '::notifications.taskCreated.outro', ['deadline' => $this->task->end_at]));
        }

        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
