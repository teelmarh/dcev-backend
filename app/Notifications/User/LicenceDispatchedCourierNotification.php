<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\Licence;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenceDispatchedCourierNotification extends Notification
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
        $detail  = $licence->deliveryDetail;

        $mail = (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Your Licence Has Been Dispatched — ' . $licence->licence_number)
            ->greeting("Hi {$notifiable->first_name},")
            ->line("Your {$licence->typeLabel()} has been approved and dispatched for courier delivery.")
            ->line("**Licence No:** {$licence->licence_number}")
            ->line("**Licence Type:** {$licence->typeLabel()}");

        if ($detail) {
            $address = "{$detail->address_line}, {$detail->city}, {$detail->state}"
                . ($detail->postal_code ? ", {$detail->postal_code}" : '');

            $mail->line("**Recipient:** {$detail->recipient_name}")
                 ->line("**Phone:** {$detail->recipient_phone}")
                 ->line("**Delivery Address:** {$address}");

            if ($detail->courier_instructions) {
                $mail->line("**Instructions:** {$detail->courier_instructions}");
            }
        }

        return $mail
            ->line('Ensure someone is available at the delivery address to receive the package. For any queries, contact your nearest NCAA DCEV office.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
