<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\RegionalOffice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerRegionController extends Controller
{
    /**
     * GET /v1/officer/regions
     * Returns all active regions with today's booking metrics.
     * Optional ?date=YYYY-MM-DD to see metrics for a specific date.
     */
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('oversee_regions')) {
            return $this->errorResponse('You do not have permission to oversee regions.', 403);
        }

        $date = $request->query('date', now()->toDateString());

        $regions = RegionalOffice::orderBy('name')->get()->map(function (RegionalOffice $region) use ($date) {
            $booked    = $region->bookedCountForDate($date);
            $capacity  = $region->daily_capacity;

            return [
                'id'                  => $region->id,
                'name'                => $region->name,
                'state'               => $region->state,
                'city'                => $region->city,
                'active'              => $region->active,
                'is_pickup_location'  => $region->is_pickup_location,
                'daily_capacity'      => $capacity,
                'working_hours_start' => $region->working_hours_start,
                'working_hours_end'   => $region->working_hours_end,
                'metrics'             => [
                    'date'              => $date,
                    'booked'            => $booked,
                    'available'         => max(0, $capacity - $booked),
                    'utilisation_pct'   => $capacity > 0 ? round(($booked / $capacity) * 100, 1) : 0,
                ],
            ];
        });

        return $this->successResponse($regions, 200, 'Region metrics retrieved.');
    }

    /**
     * GET /v1/officer/regions/appointments?region_id=X&date=Y&status=Z
     * Permission: oversee_regions
     *
     * Lists appointments for any region. Superadmin always has access.
     * Officer with oversee_regions can see any region.
     * Without the permission, officer only sees their own region (existing behaviour).
     */
    public function appointments(Request $request): JsonResponse
    {
        $user = $request->user();

        $canOversee = $user->role === 'superadmin' || $user->hasPermission('oversee_regions');

        $request->validate([
            'region_id' => ['sometimes', 'integer', 'exists:regional_offices,id'],
            'date'      => ['sometimes', 'date'],
            'status'    => ['sometimes', 'string'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Appointment::with(['licence.user', 'office'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time');

        if ($canOversee) {
            // Can filter by any region or see all
            if ($request->filled('region_id')) {
                $query->where('regional_office_id', $request->region_id);
            }
        } else {
            // Scoped to own office only
            if (! $user->regional_office_id) {
                return $this->errorResponse('Officer is not assigned to a regional office.', 422);
            }
            $query->where('regional_office_id', $user->regional_office_id);
        }

        $query
            ->when($request->filled('date'), fn ($q) => $q->whereDate('scheduled_date', $request->date))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        $appointments = $query->paginate($request->query('per_page', 20));

        return $this->successResponse(
            $appointments->through(fn ($appt) => [
                'id'             => $appt->id,
                'scheduled_date' => $appt->scheduled_date?->toDateString(),
                'scheduled_time' => $appt->scheduled_time,
                'status'         => $appt->status,
                'office'         => $appt->office ? [
                    'id'   => $appt->office->id,
                    'name' => $appt->office->name,
                    'city' => $appt->office->city,
                ] : null,
                'applicant'      => $appt->licence?->user ? [
                    'id'         => $appt->licence->user->id,
                    'first_name' => $appt->licence->user->first_name,
                    'last_name'  => $appt->licence->user->last_name,
                    'email'      => $appt->licence->user->email,
                ] : null,
                'licence_id'     => $appt->licence_id,
            ])->toArray(),
            200,
            'Appointments retrieved.'
        );
    }
}
