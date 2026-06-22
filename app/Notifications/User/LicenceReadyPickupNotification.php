<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\Licence;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LicenceReadyPickupNotification extends Notification
{
    use Queueable;

    public function __construct(private Licence $licence) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cmMgr   = new CustomMailerManager();
        $licence = $this->licence;
        $office  = $licence->pickupOffice;

        $mail = (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Your Licence Is Ready for Collection — ' . $licence->licence_number)
            ->greeting("Hi {$notifiable->first_name},")
            ->line("Great news! Your {$licence->typeLabel()} has been processed and is now ready for collection.")
            ->line('Your pickup code is:')
            ->line(new HtmlString("<h2 style='letter-spacing:6px;font-family:monospace;'>{$licence->pickup_code}</h2>"))
            ->line('Present this code at the collection counter to receive your licence card.')
            ->line("**Licence No:** {$licence->licence_number}")
            ->line("**Licence Type:** {$licence->typeLabel()}");

        if ($office) {
            $mail->line("**Collection Office:** {$office->name}")
                 ->line("**Address:** {$office->address}, {$office->city}, {$office->state}");

            if ($office->phone) {
                $mail->line("**Phone:** {$office->phone}");
            }
        }

        return $mail
            ->line('Please bring a valid government-issued ID. Late arrivals may not be accommodated.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
