<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WelcomeEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CustomMailerManager $customMailerManager, public User $user)
    {
        //
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

        $settings = ApplicationSetting::first();
        $cmMgr = new CustomMailerManager($settings->name, '#ED1450', $settings->email, $settings->logo);

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Welcome to SaveTown!')
            ->greeting("Hi {$this->user->name},")
            ->line('We’re thrilled to have you on board. Our simple-to-use medical AI speech-to-transcription app is designed to make your life easier by saving you valuable time and ensuring accurate transcriptions.')
            ->line(new HtmlString("<p>Should you have any questions or need assistance, please don’t hesitate to reach out to us at <a href='mailto:help@dorascribe.com'>help@dorascribe.com</a>. We’re here to support you every step of the way.</p>"))
            ->line('Thank you for choosing Dorascribe. We look forward to helping you streamline your transcription needs!')
            ->action('Watch Our How-To Use Guide', 'https://dorascribe.ai/tutorials');

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
