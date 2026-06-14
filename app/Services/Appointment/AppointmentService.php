<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\RegionalOffice;
use Carbon\Carbon;

class AppointmentService
{
    private const SLOTS_PER_SESSION = 48;
    private const MORNING_TIME      = '08:00';
    private const AFTERNOON_TIME    = '13:00';

    public function validateDate(RegionalOffice $office, string $date): void
    {
        $requested = Carbon::parse($date)->startOfDay();

        if ($requested->isWeekend()) {
            throw new \InvalidArgumentException('Appointments cannot be booked on weekends.');
        }


        if (! $this->hasCapacityForDate($office, $date)) {
            throw new \InvalidArgumentException(
                "No capacity available at {$office->name} on {$requested->toDateString()}. Please choose another date."
            );
        }
    }

  
    public function assignTimeSlot(RegionalOffice $office, string $date): string
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

        $morningFull    = $morningBooked >= self::SLOTS_PER_SESSION;
        $afternoonFull  = $afternoonBooked >= self::SLOTS_PER_SESSION;

        if ($morningFull) {
            return self::AFTERNOON_TIME;
        }

        if ($afternoonFull) {
            return self::MORNING_TIME;
        }

        // Both sessions have space — pick randomly
        return (bool) random_int(0, 1) ? self::MORNING_TIME : self::AFTERNOON_TIME;
    }

   
    public function availability(RegionalOffice $office, string $date): array
    {
        $totalBooked   = Appointment::where('regional_office_id', $office->id)
            ->whereDate('scheduled_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $realCapacity    = self::SLOTS_PER_SESSION * 2;          // 96
        $displayCapacity = 100;                                   // shown to client
        $realRemaining   = max(0, $realCapacity - $totalBooked);
        $displayRemaining = min($realRemaining, $displayCapacity);

        return [
            'date'      => $date,
            'office'    => $office->slug,
            'capacity'  => $displayCapacity,
            'booked'    => $totalBooked,
            'remaining' => $displayRemaining,
            'available' => $realRemaining > 0,
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
