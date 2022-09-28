<?php

namespace river\Notifications;

use river\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailTested extends Notification
{
    /**
     * @var \river\Models\User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via()
    {
        return ['mail'];
    }

    public function toMail()
    {
        return (new MailMessage())
            ->subject('river Test Message')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('This is a test of the river mail system. You\'re good to go!');
    }
}
