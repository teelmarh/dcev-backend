<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $verifyEmailLink;

    /**
     * Create a new notification instance.
     */
    public function __construct(private User $user, private string $otp)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $cmMgr = new CustomMailerManager();

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject(ucfirst($cmMgr->getName()).' Password Reset')
            ->greeting("Hi {$this->user->first_name},")
            ->line('You sent a request to reset your password. Kindly use this OTP to login.')
            ->line(new HtmlString("<h3> {$this->otp} </h3>"))
            ->line('Thank you for using '.$cmMgr->getName());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
