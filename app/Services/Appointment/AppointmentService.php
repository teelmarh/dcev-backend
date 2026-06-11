<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\Licence;
use App\Models\RegionalOffice;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Slot window duration in minutes.
     * e.g. 30 means slots at 09:00, 09:30, 10:00 …
     */
    private const SLOT_MINUTES = 30;

    /**
     * Validate that the requested date is:
     *  - not a Sunday (offices closed)
     *  - within office capacity
     *
     * Returns void or throws \InvalidArgumentException.
     */
    public function validateDate(RegionalOffice $office, string $date): void
    {
        $requested = Carbon::parse($date)->startOfDay();

        if ($requested->isSunday()) {
            throw new \InvalidArgumentException('Appointments cannot be booked on Sundays.');
        }

        if ($requested->isSaturday()) {
            throw new \InvalidArgumentException('Appointments cannot be booked on Saturdays.');
        }

        if (! $office->hasCapacityForDate($date)) {
            throw new \InvalidArgumentException(
                "No capacity available at {$office->name} on {$requested->toDateString()}. Please choose another date."
            );
        }
    }

    /**
     * Automatically assign the next available time slot on the given date.
     *
     * Divides the working day into SLOT_MINUTES-wide windows.
     * Each window accommodates floor(daily_capacity / num_slots) people.
     * The slot assigned is based on how many bookings already exist for that date.
     *
     * @return string  HH:MM format, e.g. "09:30"
     */
    public function assignTimeSlot(RegionalOffice $office, string $date): string
    {
        $start        = Carbon::parse($office->working_hours_start);
        $end          = Carbon::parse($office->working_hours_end);
        $totalMinutes = $start->diffInMinutes($end);
        $numSlots     = (int) floor($totalMinutes / self::SLOT_MINUTES);

        // Guard against misconfigured hours
        if ($numSlots < 1) {
            return $start->format('H:i');
        }

        $peoplePerSlot = max(1, (int) floor($office->daily_capacity / $numSlots));

        // Count existing bookings on that date (not cancelled)
        $booked = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $slotIndex = (int) floor($booked / $peoplePerSlot);

        // Cap at last slot so we never exceed working hours
        $slotIndex = min($slotIndex, $numSlots - 1);

        return $start->copy()->addMinutes($slotIndex * self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Return availability info for a given office + date.
     */
    public function availability(RegionalOffice $office, string $date): array
    {
        $start        = Carbon::parse($office->working_hours_start);
        $end          = Carbon::parse($office->working_hours_end);
        $totalMinutes = $start->diffInMinutes($end);
        $numSlots     = max(1, (int) floor($totalMinutes / self::SLOT_MINUTES));
        $booked       = $office->bookedCountForDate($date);
        $remaining    = max(0, $office->daily_capacity - $booked);

        // Build a per-slot breakdown so the frontend can display a timeline
        $peoplePerSlot = max(1, (int) floor($office->daily_capacity / $numSlots));
        $slots         = [];
        for ($i = 0; $i < $numSlots; $i++) {
            $slotTime      = $start->copy()->addMinutes($i * self::SLOT_MINUTES)->format('H:i');
            $slotBooked    = Appointment::where('regional_office_id', $office->id)
                ->whereDate('scheduled_date', $date)
                ->where('scheduled_time', $slotTime)
                ->whereNotIn('status', ['cancelled'])
                ->count();
            $slots[]       = [
                'time'      => $slotTime,
                'capacity'  => $peoplePerSlot,
                'booked'    => $slotBooked,
                'available' => $slotBooked < $peoplePerSlot,
            ];
        }

        return [
            'date'      => $date,
            'office'    => $office->slug,
            'capacity'  => $office->daily_capacity,
            'booked'    => $booked,
            'remaining' => $remaining,
            'available' => $remaining > 0,
            'slots'     => $slots,
        ];
    }
}
