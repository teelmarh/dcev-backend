<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OfficerWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(private User $officer, private string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cmMgr = new CustomMailerManager();

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Welcome to ' . ucfirst($cmMgr->getName()) . ' — Set Your Password')
            ->greeting("Hi {$this->officer->first_name},")
            ->line('Your officer account has been created on the NCAA DCEV platform.')
            ->line('Use the OTP below to set your password. It is valid for 6 hours.')
            ->line(new HtmlString("<h3 style='letter-spacing:6px;'>{$this->otp}</h3>"))
            ->line('If you did not expect this email, please contact your administrator.')
            ->line('Thank you, ' . $cmMgr->getName());
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
