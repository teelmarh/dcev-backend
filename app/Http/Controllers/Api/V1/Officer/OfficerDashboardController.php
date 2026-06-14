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
        $licences = Licence::with(['user', 'appointment'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        // Eager-load type-specific detail on each licence after pagination
        $licences->each(fn ($l) => $l->load($l->detailRelationName()));

        return $this->successResponse(
            OfficerLicenceResource::collection($licences)->response()->getData(true),
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

        // Load the type-specific detail relation (e.g. asoDetail, pilotDetail, etc.)
        $licence->load($licence->detailRelationName());

        // Map to a generic key so the resource doesn't need to know the relation name
        $licence->licenceDetail = $licence->{$licence->detailRelationName()};

        return $this->successResponse(new OfficerLicenceResource($licence), 200, 'Application retrieved.');
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
}
