<?php

namespace App\Notifications\User;

use App\Classes\CustomMailerManager;
use App\Classes\CustomMailMessage;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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

        return (new CustomMailMessage($cmMgr))
            ->from($cmMgr->getEmail(), $cmMgr->getName())
            ->subject('Biometric Appointment Confirmed — ' . $appt->scheduled_date->format('d M Y'))
            ->greeting("Hi {$notifiable->first_name},")
            ->line('Your biometric appointment has been confirmed. Please find the details below.')
            ->line("**Date:** {$date}")
            ->line("**Session:** {$session} — {$time}")
            ->line("**Office:** {$office->name}")
            ->line("**Address:** {$office->address}")
            ->line('Please arrive on time and bring a valid government-issued ID. Late arrivals may not be accommodated.')
            ->line('Thank you for using ' . $cmMgr->getName() . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
