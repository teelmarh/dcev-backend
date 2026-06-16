<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Appointments\AppointmentResource;
use App\Http\Resources\Licences\LicenceResource;
use App\Http\Resources\Officer\OfficerAppointmentResource;
use App\Models\Appointment;
use App\Models\Licence;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficerDashboardController extends Controller
{
    public function applications(Request $request): JsonResponse
    {
        $licences = Licence::with(['user', 'appointment'])
            ->when($request->query('application_status'), fn ($q, $s) => $q->where('application_status', $s))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        // Eager-load type-specific detail on each licence after pagination
        $licences->each(fn ($l) => $l->load($l->detailRelationName()));

        return $this->successResponse(
            LicenceResource::collection($licences)->response()->getData(true),
            200, 'Applications retrieved.'
        );
    }

    public function showApplication(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::with([
            'user',
            'appointment.office',
            'deliveryDetail',
            'enrollmentTransaction',
        ])->find($request->licence_id);

        if (! $licence) {
            return $this->errorResponse('Application not found.', 404);
        }

        $licence->load($licence->detailRelationName());

        return $this->successResponse(new LicenceResource($licence), 200, 'Application retrieved.');
    }

    public function todayAppointments(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $query = Appointment::with(['licence.user'])
            ->whereDate('scheduled_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('scheduled_time');

        if ($user->role !== 'superadmin') {
            if (! $user->regional_office_id) {
                return $this->errorResponse('Officer is not assigned to a regional office.', 422);
            }
            $query->where('regional_office_id', $user->regional_office_id);
        }

        return $this->successResponse(
            OfficerAppointmentResource::collection($query->get()),
            200,
            'Today\'s appointments retrieved.'
        );
    }

    public function appointments(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Appointment::with(['licence.user', 'office'])
            ->when($request->query('date'), fn ($q, $date) => $q->whereDate('scheduled_date', $date))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('office_id'), fn ($q, $id) => $q->where('regional_office_id', $id))
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time');

        if ($user->role !== 'superadmin') {
            if (! $user->regional_office_id) {
                return $this->errorResponse('Officer is not assigned to a regional office.', 422);
            }
            $query->where('regional_office_id', $user->regional_office_id);
        }

        return $this->successResponse(
            OfficerAppointmentResource::collection($query->paginate(20))->response()->getData(true),
            200,
            'Appointments retrieved.'
        );
    }

    /**
     * GET /v1/officer/stats?officer_id=X (superadmin scoped)
     * GET /v1/officer/stats             (own stats when called by officer)
     *
     * Returns: total processed, breakdown by action (approved/rejected/returned),
     *          average hours from initial_issue_date → processed_at.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Superadmin may pass officer_id to view any officer's stats
        if ($user->role === 'superadmin' && $request->filled('officer_id')) {
            $request->validate(['officer_id' => ['integer', 'exists:users,id']]);
            $officerId = (int) $request->officer_id;
        } else {
            $officerId = $user->id;
        }

        $processed = Licence::where('processed_by', $officerId)
            ->whereNotNull('processed_at')
            ->get(['status', 'initial_issue_date', 'processed_at']);

        $total = $processed->count();

        $byStatus = $processed->groupBy('status')->map->count();

        // Average processing time in hours: processed_at − initial_issue_date
        $avgHours = null;
        if ($total > 0) {
            $totalHours = $processed->sum(
                fn ($l) => $l->initial_issue_date && $l->processed_at
                    ? $l->initial_issue_date->diffInHours($l->processed_at)
                    : 0
            );
            $avgHours = round($totalHours / $total, 1);
        }

        $officer = User::find($officerId, ['id', 'first_name', 'last_name', 'email', 'role']);

        return $this->successResponse([
            'officer'              => $officer,
            'total_processed'      => $total,
            'by_status'            => $byStatus,
            'avg_processing_hours' => $avgHours,
        ], 200, 'Officer stats retrieved.');
    }

    /**
     * PATCH /v1/officer/appointments/mark-attended
     * Body: appointment_id, attended (bool)
     *
     * Mark whether an applicant showed up for their appointment.
     * Sets attended_at = now() when attended = true, null when attended = false.
     * Only the office's assigned officer or superadmin can call this.
     */
    public function markAttended(Request $request): JsonResponse
    {
        $data = $request->validate([
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'attended'       => ['required', 'boolean'],
        ]);

        $appointment = Appointment::with('office')->find($data['appointment_id']);
        $user        = $request->user();

        // Officers can only mark attendance for their own office
        if ($user->role !== 'superadmin' && $appointment->regional_office_id !== $user->regional_office_id) {
            return $this->errorResponse('This appointment is not at your office.', 403);
        }

        $appointment->update([
            'attended_at' => $data['attended'] ? now() : null,
        ]);

        return $this->successResponse(
            new AppointmentResource($appointment->fresh('office')),
            200,
            $data['attended'] ? 'Applicant marked as attended.' : 'Attendance cleared.'
        );
    }
}

