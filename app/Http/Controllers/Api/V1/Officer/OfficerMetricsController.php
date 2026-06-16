<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Licence;
use App\Models\RegionalOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OfficerMetricsController extends Controller
{
    /**
     * GET /v1/officer/metrics
     *
     * Returns dashboard metrics for the authenticated officer.
     *
     * Scope rules:
     *  - Superadmin              → always national (all regions)
     *  - view_national_stats     → national (all regions)
     *  - Everyone else           → restricted to their assigned regional_office_id
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $isNational = $user->role === 'superadmin'
            || $user->hasPermission('view_national_stats');

        if (! $isNational && ! $user->regional_office_id) {
            return $this->errorResponse(
                'You are not assigned to a regional office. Contact your administrator.',
                422
            );
        }

        $officeId = $user->regional_office_id;

        /*
         * Closure that scopes an appointment query to the officer's office
         * when the caller is not national.
         */
        $scope = function (Builder $q) use ($isNational, $officeId): Builder {
            return $isNational
                ? $q
                : $q->where('regional_office_id', $officeId);
        };

        $today = now()->toDateString();

        $totalApplicants = Appointment::tap($scope)->distinct('user_id')->count('user_id');

        $totalApplications = Appointment::tap($scope)->distinct('licence_id')->count('licence_id');
        $byStatus = Licence::select('licences.application_status', DB::raw('COUNT(DISTINCT licences.id) as total'))
            ->join('appointments', 'appointments.licence_id', '=', 'licences.id')
            ->when(! $isNational, fn ($q) => $q->where('appointments.regional_office_id', $officeId))
            ->groupBy('licences.application_status')
            ->orderBy('licences.application_status')
            ->pluck('total', 'application_status')
            ->map(fn ($v) => (int) $v);

        $scheduledAppointments = Appointment::tap($scope)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('scheduled_date', '>=', $today)
            ->count();

        $completedAppointments = Appointment::tap($scope)
            ->where('status', 'completed')
            ->count();

        $missedAppointments = Appointment::tap($scope)
            ->whereDate('scheduled_date', '<', $today)
            ->whereNull('attended_at')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        $activeCentres = RegionalOffice::active()
            ->when(! $isNational, fn ($q) => $q->where('id', $officeId))
            ->count();

        // ------------------------------------------------------------------
        // 8. Delivery stats — pickup vs courier counts + monthly trend
        // ------------------------------------------------------------------
        $deliveryBase = Licence::where('application_status', 'approved')
            ->whereNotNull('delivery_method')
            ->when(
                ! $isNational,
                fn ($q) => $q->whereHas(
                    'appointment',
                    fn ($a) => $a->where('regional_office_id', $officeId)
                )
            );

        $pickupCount  = (clone $deliveryBase)->where('delivery_method', 'pickup')->count();
        $courierCount = (clone $deliveryBase)->where('delivery_method', 'delivery')->count();

        // Last 6 months: group by month + delivery_method (SQL Server FORMAT)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

        $trendRows = Licence::select(
                DB::raw("FORMAT(processed_at, 'yyyy-MM') as month"),
                'delivery_method',
                DB::raw('COUNT(*) as total')
            )
            ->where('application_status', 'approved')
            ->whereNotNull('delivery_method')
            ->where('processed_at', '>=', $sixMonthsAgo)
            ->when(
                ! $isNational,
                fn ($q) => $q->whereHas(
                    'appointment',
                    fn ($a) => $a->where('regional_office_id', $officeId)
                )
            )
            ->groupBy(DB::raw("FORMAT(processed_at, 'yyyy-MM')"), 'delivery_method')
            ->orderBy('month')
            ->get();

        // Pivot into [ { month, pickup, delivery }, ... ] format
        $trendMap = [];
        foreach ($trendRows as $row) {
            $m = $row->month;
            if (! isset($trendMap[$m])) {
                $trendMap[$m] = ['month' => $m, 'pickup' => 0, 'delivery' => 0];
            }
            $trendMap[$m][$row->delivery_method] = (int) $row->total;
        }
        $monthlyTrend = array_values($trendMap);

        // ------------------------------------------------------------------
        // 9. Per-region breakdown (only meaningful for national callers,
        //    but always returned — regional caller just gets their one row)
        // ------------------------------------------------------------------
        $regions = RegionalOffice::select('id', 'name', 'state', 'city', 'is_pickup_location', 'active')
            ->when(! $isNational, fn ($q) => $q->where('id', $officeId))
            ->get()
            ->map(function (RegionalOffice $office) use ($today) {
                $oid = $office->id;

                $applicants = Appointment::where('regional_office_id', $oid)
                    ->distinct('user_id')->count('user_id');

                $applications = Appointment::where('regional_office_id', $oid)
                    ->distinct('licence_id')->count('licence_id');

                $byStatusRegion = Licence::select('licences.application_status', DB::raw('COUNT(DISTINCT licences.id) as total'))
                    ->join('appointments', 'appointments.licence_id', '=', 'licences.id')
                    ->where('appointments.regional_office_id', $oid)
                    ->groupBy('licences.application_status')
                    ->pluck('total', 'application_status')
                    ->map(fn ($v) => (int) $v);

                $scheduled = Appointment::where('regional_office_id', $oid)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->whereDate('scheduled_date', '>=', $today)
                    ->count();

                $completed = Appointment::where('regional_office_id', $oid)
                    ->where('status', 'completed')
                    ->count();

                $missed = Appointment::where('regional_office_id', $oid)
                    ->whereDate('scheduled_date', '<', $today)
                    ->whereNull('attended_at')
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->count();

                return [
                    'office_id'              => $office->id,
                    'name'                   => $office->name,
                    'state'                  => $office->state,
                    'city'                   => $office->city,
                    'is_pickup_location'     => $office->is_pickup_location,
                    'active'                 => $office->active,
                    'total_applicants'       => $applicants,
                    'total_applications'     => $applications,
                    'applications_by_status' => $byStatusRegion,
                    'scheduled_appointments' => $scheduled,
                    'completed_appointments' => $completed,
                    'missed_appointments'    => $missed,
                ];
            });

        return $this->successResponse([
            'scope'                  => $isNational ? 'national' : 'regional',
            'regional_office_id'     => $isNational ? null : $officeId,
            'total_applicants'       => $totalApplicants,
            'total_applications'     => $totalApplications,
            'applications_by_status' => $byStatus,
            'scheduled_appointments' => $scheduledAppointments,
            'completed_appointments' => $completedAppointments,
            'missed_appointments'    => $missedAppointments,
            'active_centres'         => $activeCentres,
            'delivery'               => [
                'pickup_count'   => $pickupCount,
                'courier_count'  => $courierCount,
                'monthly_trend'  => $monthlyTrend,
            ],
            'regions'                => $regions,
        ], 200, 'Dashboard metrics retrieved.');
    }
}
