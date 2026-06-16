<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\Licence;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

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

        $addressBlock = $detail ? "
            <table style=\"width:100%;border-collapse:collapse;margin:20px 0;\">
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Recipient</td>
                    <td style=\"padding:10px 0;font-weight:600;\">{$detail->recipient_name}</td>
                </tr>
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Phone</td>
                    <td style=\"padding:10px 0;\">{$detail->recipient_phone}</td>
                </tr>
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Delivery Address</td>
                    <td style=\"padding:10px 0;\">{$detail->address_line}, {$detail->city}, {$detail->state}"
                    . ($detail->postal_code ? " {$detail->postal_code}" : '') . "</td>
                </tr>"
                . ($detail->courier_instructions ? "
                <tr>
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Instructions</td>
                    <td style=\"padding:10px 0;\">{$detail->courier_instructions}</td>
                </tr>" : '') . "
            </table>" : '';

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Your Licence Has Been Dispatched — ' . $licence->licence_number)
            ->greeting("Hi {$notifiable->first_name},")
            ->line('Your aviation licence has been approved and dispatched for courier delivery. Please find the delivery details below.')
            ->line(new HtmlString($addressBlock))
            ->line('Ensure someone is available at the delivery address to receive the package. If you have questions about your delivery, please contact your nearest NCAA DCEV office.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
