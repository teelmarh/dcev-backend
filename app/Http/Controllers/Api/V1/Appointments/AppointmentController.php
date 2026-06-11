<?php

namespace App\Http\Controllers\Api\V1\Appointments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\RescheduleAppointmentRequest;
use App\Http\Requests\Appointments\ShowAppointmentRequest;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\Appointments\AppointmentResource;
use App\Http\Resources\Appointments\RegionalOfficeResource;
use App\Models\Appointment;
use App\Models\Licence;
use App\Models\RegionalOffice;
use App\Services\Appointment\AppointmentService;
use App\Traits\Api\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use ApiResponder;

    public function __construct(private AppointmentService $appointmentService) {}

    /**
     * GET /v1/appointments/offices
     */
    public function offices(): JsonResponse
    {
        $offices = RegionalOffice::active()->get();

        return $this->dataResponse(
            RegionalOfficeResource::collection($offices),
            'Regional offices retrieved.',
            true,
            200
        );
    }

    /**
     * GET /v1/appointments/availability/{office}/{date}
     */
    public function availability(Request $request, string $office, string $date): JsonResponse
    {
        $officeModel = RegionalOffice::where('slug', $office)->where('active', true)->firstOrFail();

        $data = $this->appointmentService->availability($officeModel, $date);

        return $this->dataResponse($data, 'Availability retrieved.', true, 200);
    }

    /**
     * GET /v1/appointments
     */
    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::with('office')
            ->where('user_id', $request->user()->id)
            ->latest('scheduled_date')
            ->get();

        return $this->dataResponse(
            AppointmentResource::collection($appointments),
            'Appointments retrieved.',
            true,
            200
        );
    }

    /**
     * GET /v1/appointments/{appointment}
     */
    public function show(ShowAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::find($request->validated('appointment_id'));

        if (! $appointment) {
            return $this->errorResponse('Appointment not found.', 404);
        }

        if ($appointment->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->dataResponse(
            new AppointmentResource($appointment->load('office')),
            'Appointment retrieved.',
            true,
            200
        );
    }

    /**
     * POST /v1/appointments
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $licence   = Licence::findOrFail($validated['licence_id']);
        $office    = RegionalOffice::findOrFail($validated['regional_office_id']);

        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        // Prevent duplicate active appointment for the same licence
        $existing = Appointment::where('licence_id', $licence->id)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            return $this->dataResponse(
                new AppointmentResource($existing->load('office')),
                'An active appointment already exists for this licence.',
                false,
                422
            );
        }

        try {
            $this->appointmentService->validateDate($office, $validated['scheduled_date']);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        $assignedTime = $this->appointmentService->assignTimeSlot($office, $validated['scheduled_date']);

        $appointment = Appointment::create([
            'user_id'            => $request->user()->id,
            'licence_id'         => $licence->id,
            'regional_office_id' => $office->id,
            'scheduled_date'     => $validated['scheduled_date'],
            'scheduled_time'     => $assignedTime,
            'status'             => 'pending',
            'notes'              => $validated['notes'] ?? null,
        ]);

        return $this->dataResponse(
            new AppointmentResource($appointment->load('office')),
            'Appointment booked successfully.',
            true,
            201
        );
    }

    /**
     * PATCH /v1/appointments/reschedule
     */
    public function reschedule(RescheduleAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::find($request->validated('appointment_id'));

        if (! $appointment) {
            return $this->errorResponse('Appointment not found.', 404);
        }

        if ($appointment->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if (! $appointment->isReschedulable()) {
            return $this->errorResponse('This appointment cannot be rescheduled.', 422);
        }

        $validated = $request->validated();
        $office    = isset($validated['regional_office_id'])
            ? RegionalOffice::findOrFail($validated['regional_office_id'])
            : $appointment->office;

        try {
            $this->appointmentService->validateDate($office, $validated['scheduled_date']);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        $assignedTime = $this->appointmentService->assignTimeSlot($office, $validated['scheduled_date']);

        $appointment->update([
            'previous_date'      => $appointment->scheduled_date,
            'previous_time'      => $appointment->scheduled_time,
            'scheduled_date'     => $validated['scheduled_date'],
            'scheduled_time'     => $assignedTime,
            'regional_office_id' => $office->id,
            'status'             => 'rescheduled',
            'notes'              => $validated['notes'] ?? $appointment->notes,
        ]);

        return $this->dataResponse(
            new AppointmentResource($appointment->fresh()->load('office')),
            'Appointment rescheduled.',
            true,
            200
        );
    }

    /**
     * PATCH /v1/appointments/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $appointment = Appointment::find($request->integer('appointment_id'));

        if (! $appointment) {
            return $this->errorResponse('Appointment not found.', 404);
        }

        if ($appointment->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if (! $appointment->isCancellable()) {
            return $this->errorResponse('This appointment cannot be cancelled.', 422);
        }

        $appointment->update(['status' => 'cancelled']);

        return $this->dataResponse(
            new AppointmentResource($appointment->fresh()->load('office')),
            'Appointment cancelled.',
            true,
            200
        );
    }
}
