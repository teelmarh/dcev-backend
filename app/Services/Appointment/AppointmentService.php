<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\Licence;
use App\Models\RegionalOffice;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Earliest bookable date: 7 days after the licence application date.
     */
    public function earliestBookableDate(Licence $licence): Carbon
    {
        return $licence->created_at->copy()->addDays(7)->startOfDay();
    }

    /**
     * Validate that the requested date is:
     *  - a valid date
     *  - at least 7 days after the licence application
     *  - not a Sunday (offices closed)
     *  - within office capacity
     *
     * Returns true or throws \InvalidArgumentException.
     */
    public function validateDate(RegionalOffice $office, Licence $licence, string $date): void
    {
        $requested = Carbon::parse($date)->startOfDay();
        $earliest  = $this->earliestBookableDate($licence);

        if ($requested->lt($earliest)) {
            throw new \InvalidArgumentException(
                'Appointment date must be at least 7 days after your application date. Earliest available: ' . $earliest->toDateString()
            );
        }

        if ($requested->isSunday()) {
            throw new \InvalidArgumentException('Appointments cannot be booked on Sundays.');
        }

        if ($requested->isWeekend()) {
            throw new \InvalidArgumentException('Appointments cannot be booked on weekends.');
        }

        if (! $office->hasCapacityForDate($date)) {
            throw new \InvalidArgumentException(
                "No capacity available at {$office->name} on {$requested->toDateString()}. Please choose another date."
            );
        }
    }

    /**
     * Return availability info for a given office + date.
     */
    public function availability(RegionalOffice $office, string $date): array
    {
        $booked    = $office->bookedCountForDate($date);
        $remaining = max(0, $office->daily_capacity - $booked);

        return [
            'date'       => $date,
            'office'     => $office->slug,
            'capacity'   => $office->daily_capacity,
            'booked'     => $booked,
            'remaining'  => $remaining,
            'available'  => $remaining > 0,
        ];
    }
}
