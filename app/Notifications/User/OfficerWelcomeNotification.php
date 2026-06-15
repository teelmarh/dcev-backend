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

        $setPasswordUrl = rtrim(config('app.url'), '/') . '/set-password?email=' . rawurlencode($this->officer->email);

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Welcome to ' . ucfirst($cmMgr->getName()) . ' — Set Your Password')
            ->greeting("Hi {$this->officer->first_name},")
            ->line('Your officer account has been created on the NCAA DCEV platform.')
            ->line('Use the one-time code below along with the button to set your password. It is valid for **6 hours**.')
            ->line(new HtmlString(
                "<div style='text-align:center;margin:24px 0;'>"
                . "<span style='display:inline-block;font-size:28px;font-weight:700;letter-spacing:8px;"
                . "padding:12px 24px;background:#f4f4f4;border-radius:6px;border:1px solid #e0e0e0;'>"
                . "{$this->otp}</span></div>"
            ))
            ->action('Set Your Password', $setPasswordUrl)
            ->line('If the button does not work, copy and paste this link into your browser:')
            ->line(new HtmlString("<a href='{$setPasswordUrl}' style='color:#1a56db;word-break:break-all;'>{$setPasswordUrl}</a>"))
            ->line('If you did not expect this email, please contact your administrator.')
            ->salutation('Thank you, ' . $cmMgr->getName());
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
