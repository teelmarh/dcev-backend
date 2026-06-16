<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfficerApplicationController extends Controller
{
    /**
     * GET /v1/officer/queue
     *
     * Applications the calling officer has claimed (under_review, processed_by = me).
     * These are in-progress — not yet processed_at.
     * Supports filtering by type; always scoped to the authenticated officer.
     */
    public function queue(Request $request): JsonResponse
    {
        $request->validate([
            'type'     => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $licences = Licence::with(['user', 'appointment.office', 'processedBy'])
            ->where('processed_by', $request->user()->id)
            ->whereNull('processed_at')                    // not yet finalised
            ->whereIn('application_status', ['under_review', 'under_enrollment', 'discrepancy_flagged', 'escalated'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest('updated_at')
            ->paginate($request->query('per_page', 20));

        $licences->each(fn ($l) => $l->load($l->detailRelationName()));

        return $this->successResponse(
            LicenceResource::collection($licences)->response()->getData(true),
            200,
            'Your application queue retrieved.'
        );
    }

    /**
     * POST /v1/officer/applications/claim
     * Body: licence_id
     *
     * Officer claims an application — marks it as under_review and assigns themselves.
     * A submitted application that has not been claimed by another officer can be claimed.
     */
    public function claim(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::find($request->licence_id);

        if ($licence->application_status === 'under_review' && $licence->processed_by !== $request->user()->id) {
            return $this->errorResponse('This application is already being reviewed by another officer.', 409);
        }

        if (in_array($licence->application_status, ['approved', 'rejected', 'returned'])) {
            return $this->errorResponse('This application has already been processed and cannot be claimed.', 422);
        }

        $licence->update([
            'application_status' => 'under_review',
            'processed_by'       => $request->user()->id,
        ]);

        $licence->appointment()->whereNull('attended_at')->update(['attended_at' => now()]);

        AuditLogger::log($request->user(), AuditLogger::APPLICATION_CLAIMED, $licence, [], $request);

        $licence->load($licence->detailRelationName(), 'user', 'appointment.office', 'deliveryDetail', 'enrollmentTransaction');

        return $this->successResponse(new LicenceResource($licence), 200, 'Application claimed. You are now reviewing it.');
    }

    /**
     * POST /v1/officer/applications/process
     * Body: licence_id, action (approved|rejected|returned), notes (optional)
     * Permission: process_application
     *
     * Finalises the review of a claimed application.
     * Only the officer who claimed it (or a superadmin) can process it.
     */
    public function process(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('process_application')) {
            return $this->errorResponse('You do not have permission to process applications.', 403);
        }

        $data = $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
            'action'     => ['required', 'string', 'in:approved,rejected,returned'],
            'notes'      => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $licence = Licence::find($data['licence_id']);

        // Only the officer who claimed it can process it (superadmin can override)
        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse(
                $licence->processed_by
                    ? 'This application is assigned to a different officer.'
                    : 'You must claim this application before processing it.',
                403
            );
        }

        if (in_array($licence->application_status, ['approved', 'rejected', 'returned'])) {
            return $this->errorResponse('This application has already been finalised.', 422);
        }

        if ($licence->application_status !== 'biometric_captured') {
            return $this->errorResponse(
                'Biometric capture must be completed before a final decision can be made. Current status: ' . $licence->application_status,
                422
            );
        }

        $licence->update([
            'application_status' => $data['action'],
            'processed_by'       => $user->id,
            'processed_at'       => now(),
            'processing_notes'   => $data['notes'] ?? null,
        ]);

        // On approval: complete the appointment + generate pickup code if applicable
        if ($data['action'] === 'approved') {
            $licence->appointment()->update(['status' => 'completed']);

            if ($licence->delivery_method === 'pickup' && ! $licence->pickup_code) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (Licence::where('pickup_code', $code)->exists());

                $licence->update(['pickup_code' => $code]);
            }
        }

        AuditLogger::log($user, AuditLogger::APPLICATION_PROCESSED, $licence, [
            'action' => $data['action'],
            'notes'  => $data['notes'] ?? null,
        ], $request);

        $licence->load($licence->detailRelationName(), 'user', 'processedBy');

        return $this->successResponse(new LicenceResource($licence), 200, "Application {$data['action']}.");
    }

    /**
     * POST /v1/officer/applications/unclaim
     * Body: licence_id
     *
     * Release an application back to the submitted pool so another officer can pick it up.
     * Only the officer who claimed it (or superadmin) can unclaim.
     */
    public function unclaim(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::find($request->licence_id);
        $user    = $request->user();

        if ($licence->application_status !== 'under_review') {
            return $this->errorResponse('Only an application under review can be unclaimed.', 422);
        }

        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse('You cannot unclaim an application assigned to another officer.', 403);
        }

        $licence->update([
            'application_status' => 'submitted',
            'processed_by'       => null,
        ]);

        $licence->appointment()->update(['attended_at' => null]);

        AuditLogger::log($user, AuditLogger::APPLICATION_UNCLAIMED, $licence, [], $request);

        return $this->showMessage('Application returned to the unassigned pool.', 200);
    }
}
