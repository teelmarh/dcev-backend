<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\RegionalOffice;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Each session holds 48 people.
     * Morning fills first; afternoon opens once morning is full.
     */
    private const SLOTS_PER_SESSION = 48;
    private const MORNING_TIME      = '08:00';
    private const AFTERNOON_TIME    = '13:00';

    /**
     * Validate that the requested date is:
     *  - a weekday (Mon–Sat, no Sundays)
     *  - still has capacity across both sessions
     *
     * Throws \InvalidArgumentException on failure.
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

        if (! $this->hasCapacityForDate($office, $date)) {
            throw new \InvalidArgumentException(
                "No capacity available at {$office->name} on {$requested->toDateString()}. Please choose another date."
            );
        }
    }

    /**
     * Return the assigned session time for a new booking.
     * Morning (08:00) fills first up to SLOTS_PER_SESSION,
     * then afternoon (13:00) opens.
     */
    public function assignTimeSlot(RegionalOffice $office, string $date): string
    {
        $morningBooked = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->where('scheduled_time', self::MORNING_TIME)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return $morningBooked < self::SLOTS_PER_SESSION
            ? self::MORNING_TIME
            : self::AFTERNOON_TIME;
    }

    /**
     * Return availability info for a given office + date, broken into sessions.
     */
    public function availability(RegionalOffice $office, string $date): array
    {
        $morningBooked = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->where('scheduled_time', self::MORNING_TIME)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $afternoonBooked = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->where('scheduled_time', self::AFTERNOON_TIME)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $totalBooked = $morningBooked + $afternoonBooked;
        $totalCapacity = self::SLOTS_PER_SESSION * 2;

        return [
            'date'   => $date,
            'office' => $office->slug,
            'sessions' => [
                'morning' => [
                    'time'      => self::MORNING_TIME,
                    'capacity'  => self::SLOTS_PER_SESSION,
                    'booked'    => $morningBooked,
                    'remaining' => max(0, self::SLOTS_PER_SESSION - $morningBooked),
                    'available' => $morningBooked < self::SLOTS_PER_SESSION,
                ],
                'afternoon' => [
                    'time'      => self::AFTERNOON_TIME,
                    'capacity'  => self::SLOTS_PER_SESSION,
                    'booked'    => $afternoonBooked,
                    'remaining' => max(0, self::SLOTS_PER_SESSION - $afternoonBooked),
                    'available' => $afternoonBooked < self::SLOTS_PER_SESSION,
                ],
            ],
            'total_capacity'  => $totalCapacity,
            'total_booked'    => $totalBooked,
            'total_remaining' => max(0, $totalCapacity - $totalBooked),
            'available'       => $totalBooked < $totalCapacity,
        ];
    }

    // -------------------------------------------------------------------------

    private function hasCapacityForDate(RegionalOffice $office, string $date): bool
    {
        $total = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return $total < (self::SLOTS_PER_SESSION * 2);
    }
}
