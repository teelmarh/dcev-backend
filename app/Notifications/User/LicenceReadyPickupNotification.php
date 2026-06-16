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
        $code    = $licence->pickup_code;

        $codeBlock = "
            <div style=\"text-align:center;margin:28px 0;\">
                <p style=\"margin:0 0 8px;color:#888;font-size:13px;text-transform:uppercase;letter-spacing:1px;\">Your Pickup Code</p>
                <div style=\"display:inline-block;background:#f4f4f4;border:2px dashed #ccc;border-radius:8px;padding:16px 32px;\">
                    <span style=\"font-size:32px;font-weight:700;letter-spacing:6px;color:#1a1a1a;font-family:monospace;\">{$code}</span>
                </div>
                <p style=\"margin:10px 0 0;color:#888;font-size:12px;\">Present this code at the office to collect your licence.</p>
            </div>";

        $officeBlock = $office ? "
            <table style=\"width:100%;border-collapse:collapse;margin:20px 0;\">
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Office</td>
                    <td style=\"padding:10px 0;font-weight:600;\">{$office->name}</td>
                </tr>
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Address</td>
                    <td style=\"padding:10px 0;\">{$office->address}, {$office->city}, {$office->state}</td>
                </tr>"
                . ($office->phone ? "
                <tr>
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Phone</td>
                    <td style=\"padding:10px 0;\">{$office->phone}</td>
                </tr>" : '') . "
            </table>" : '';

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Your Licence Is Ready for Collection — ' . $licence->licence_number)
            ->greeting("Hi {$notifiable->first_name},")
            ->line('Great news! Your aviation licence has been processed and is now ready for collection.')
            ->line(new HtmlString($codeBlock))
            ->line(new HtmlString($officeBlock))
            ->line('Please visit the office during working hours and bring a valid government-issued ID. Quote your pickup code at the front desk.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
