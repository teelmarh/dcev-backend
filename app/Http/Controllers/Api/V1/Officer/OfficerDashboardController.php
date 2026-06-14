<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Officer\OfficerAppointmentResource;
use App\Http\Resources\Officer\OfficerLicenceResource;
use App\Models\Appointment;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerDashboardController extends Controller
{
    public function applications(Request $request): JsonResponse
    {
        $query = Licence::with(['user', 'appointment'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->latest();

        $licences = $query->paginate(20);

        return $this->successResponse(
            OfficerLicenceResource::collection($licences)->response()->getData(true),
            200, 'Applications retrieved.'
        );
    }

    public function showApplication(int $licence): JsonResponse
    {
        $licence = Licence::with([
            'user',
            'appointment.office',
            'deliveryDetail',
            'enrollmentTransaction',
        ])->find($licence);

        if (! $licence) {
            return $this->errorResponse('Application not found.', 404);
        }

        return $this->successResponse(new OfficerLicenceResource($licence), 200, 'Application retrieved.');
    }

   
    public function todayAppointments(Request $request): JsonResponse
    {
        $officeId = $request->user()->regional_office_id;

        if (! $officeId) {
            return $this->errorResponse('Officer is not assigned to a regional office.', 422);
        }

        $today = now()->toDateString();

        $appointments = Appointment::with(['licence.user'])
            ->where('regional_office_id', $officeId)
            ->whereDate('scheduled_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('scheduled_time')
            ->get();

        return $this->successResponse(OfficerAppointmentResource::collection($appointments), 200, 'Today\'s appointments retrieved.');
    }

    /**
     * GET /v1/officer/appointments
     * Paginated appointments at the officer's office, optional date filter.
     */
    public function appointments(Request $request): JsonResponse
    {
        $officeId = $request->user()->regional_office_id;

        if (! $officeId) {
            return $this->errorResponse('Officer is not assigned to a regional office.', 422);
        }

        $appointments = Appointment::with(['licence.user'])
            ->where('regional_office_id', $officeId)
            ->when($request->query('date'), fn ($q, $date) => $q->whereDate('scheduled_date', $date))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->paginate(20);

        return $this->successResponse(
            OfficerAppointmentResource::collection($appointments)->response()->getData(true),
            200, 'Appointments retrieved.'
        );
    }
}
