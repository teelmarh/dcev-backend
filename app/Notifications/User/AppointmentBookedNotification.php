<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AppointmentBookedNotification extends Notification
{
    use Queueable;

    public function __construct(private Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cmMgr   = new CustomMailerManager();
        $appt    = $this->appointment;
        $office  = $appt->office;
        $date    = $appt->scheduled_date->format('l, d F Y');
        $isMorning = $appt->scheduled_time === '08:00';
        $session = $isMorning ? 'Morning Session' : 'Afternoon Session';
        $time    = $isMorning ? '8:00 AM' : '1:00 PM';

        $detailsTable = "
            <table style=\"width:100%;border-collapse:collapse;margin:20px 0;\">
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Date</td>
                    <td style=\"padding:10px 0;font-weight:600;\">{$date}</td>
                </tr>
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Time</td>
                    <td style=\"padding:10px 0;font-weight:600;\">{$session} &mdash; {$time}</td>
                </tr>
                <tr style=\"border-bottom:1px solid #eee;\">
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Office</td>
                    <td style=\"padding:10px 0;\">{$office->name}</td>
                </tr>
                <tr>
                    <td style=\"padding:10px 0;color:#888;font-size:13px;\">Address</td>
                    <td style=\"padding:10px 0;\">{$office->address}</td>
                </tr>
            </table>";

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Biometric Appointment Confirmed — ' . $appt->scheduled_date->format('d M Y'))
            ->greeting("Hi {$notifiable->first_name},")
            ->line('Your biometric appointment has been confirmed. Please find the details below.')
            ->line(new HtmlString($detailsTable))
            ->line('Please arrive on time and bring a valid government-issued ID. Late arrivals may not be accommodated.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
