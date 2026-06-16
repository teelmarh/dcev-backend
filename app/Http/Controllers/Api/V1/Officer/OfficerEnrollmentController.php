<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Licences\LicenceResource;
use App\Http\Resources\Officer\EnrollmentVerificationResource;
use App\Models\AuditLog;
use App\Models\EnrollmentVerification;
use App\Models\Licence;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerEnrollmentController extends Controller
{
     
    public function enroll(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('process_application')) {
            return $this->errorResponse('You do not have permission to process applications.', 403);
        }

        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::find($request->licence_id);

        // Block if locked by a different officer
        if (
            in_array($licence->application_status, ['under_enrollment', 'verification_complete', 'biometric_captured', 'completed'])
            && $licence->processed_by !== null
            && $licence->processed_by !== $user->id
        ) {
            return $this->errorResponse('This application is locked by another officer.', 409);
        }

        // Block if already fully finalised
        if (in_array($licence->application_status, ['approved', 'rejected', 'returned', 'completed'])) {
            return $this->errorResponse('This application has already been finalised.', 422);
        }

        if ($licence->application_status !== 'under_enrollment' || $licence->processed_by !== $user->id) {
            $licence->update([
                'application_status' => 'under_enrollment',
                'processed_by'       => $user->id,
            ]);

            AuditLogger::log($user, AuditLogger::ENROLL_STARTED, $licence, [
                'previous_status' => $licence->getOriginal('application_status'),
            ], $request);
        }

        $verification = EnrollmentVerification::firstOrCreate(
            ['licence_id' => $licence->id],
            ['officer_id' => $user->id]
        );

        $licence->load($licence->detailRelationName(), 'user', 'appointment.office', 'deliveryDetail', 'enrollmentTransaction', 'enrollmentVerification.officer');

        return $this->successResponse([
            'licence'      => new LicenceResource($licence),
            'verification' => new EnrollmentVerificationResource($verification->fresh('officer')),
        ], 200, 'Enrollment started.');
    }

    /**
     * POST /v1/officer/enrollment/verify
     * Permission: process_application
     *
     */
    public function verify(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('process_application')) {
            return $this->errorResponse('You do not have permission to process applications.', 403);
        }

        $data = $request->validate([
            'licence_id'                  => ['required', 'integer', 'exists:licences,id'],
            'physical_presence_confirmed' => ['sometimes', 'nullable', 'boolean'],
            'nin_photo_matched'           => ['sometimes', 'nullable', 'boolean'],
            'age_eligible'                => ['sometimes', 'nullable', 'boolean'],
            'uploaded_licence_reviewed'   => ['sometimes', 'nullable', 'boolean'],
            'physical_licence_confirmed'  => ['sometimes', 'nullable', 'boolean'],
            'has_discrepancy'             => ['sometimes', 'boolean'],
            'discrepancy_type'            => ['sometimes', 'nullable', 'string', 'in:photo_mismatch,age_issue,document_invalid,licence_mismatch,other'],
            'discrepancy_remarks'         => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $licence = Licence::find($data['licence_id']);

        if (! in_array($licence->application_status, ['under_enrollment', 'discrepancy_flagged'])) {
            return $this->errorResponse('Application must be under enrollment before verification can be saved.', 422);
        }

        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse('This application is assigned to a different officer.', 403);
        }

        $verification = EnrollmentVerification::firstOrCreate(
            ['licence_id' => $licence->id],
            ['officer_id' => $user->id]
        );

        $updateData = collect($data)->except('licence_id')->toArray();
        $verification->update($updateData);

        // Determine application_status side-effect
        $actionLogged = AuditLogger::VERIFICATION_SAVED;

        if (array_key_exists('has_discrepancy', $updateData) && $updateData['has_discrepancy']) {
            $licence->update(['application_status' => 'discrepancy_flagged']);
            $actionLogged = AuditLogger::DISCREPANCY_FLAGGED;
        } elseif (
            array_key_exists('has_discrepancy', $updateData)
            && ! $updateData['has_discrepancy']
            && $licence->application_status === 'discrepancy_flagged'
        ) {
            // Officer cleared the discrepancy flag — return to under_enrollment
            $licence->update(['application_status' => 'under_enrollment']);
        }

        AuditLogger::log($user, $actionLogged, $licence, $updateData, $request);

        return $this->successResponse(
            new EnrollmentVerificationResource($verification->fresh('officer')),
            200,
            'Verification saved.'
        );
    }

    /**
     * POST /v1/officer/enrollment/complete-verification
     * Permission: process_application
     *
     */
    public function completeVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('process_application')) {
            return $this->errorResponse('You do not have permission to process applications.', 403);
        }

        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::find($request->licence_id);

        if ($licence->application_status !== 'under_enrollment') {
            return $this->errorResponse('Application must be under enrollment to complete verification.', 422);
        }

        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse('This application is assigned to a different officer.', 403);
        }

        $verification = EnrollmentVerification::where('licence_id', $licence->id)->first();

        if (! $verification || ! $verification->allChecksPass()) {
            return $this->errorResponse('All five verification checks must be confirmed before proceeding.', 422);
        }

        if ($verification->has_discrepancy) {
            return $this->errorResponse('Resolve the flagged discrepancy before completing verification.', 422);
        }

        $verification->update(['verified_at' => now()]);
        $licence->update(['application_status' => 'verification_complete']);

        AuditLogger::log($user, AuditLogger::VERIFICATION_COMPLETE, $licence, [], $request);

        $licence->load($licence->detailRelationName(), 'user', 'enrollmentVerification.officer');

        return $this->successResponse([
            'licence'      => new LicenceResource($licence),
            'verification' => new EnrollmentVerificationResource($verification->fresh('officer')),
        ], 200, 'Verification complete. You may proceed to biometric capture.');
    }

    /**
     * POST /v1/officer/enrollment/escalate
     * Permission: process_application
     *
     * Pause the enrollment and hand it up the chain.
     * Body: licence_id, escalation_reason (required)
     */
    public function escalate(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('process_application')) {
            return $this->errorResponse('You do not have permission to process applications.', 403);
        }

        $data = $request->validate([
            'licence_id'        => ['required', 'integer', 'exists:licences,id'],
            'escalation_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $licence = Licence::find($data['licence_id']);

        if (in_array($licence->application_status, ['approved', 'rejected', 'returned', 'completed'])) {
            return $this->errorResponse('This application has already been finalised and cannot be escalated.', 422);
        }

        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse('This application is assigned to a different officer.', 403);
        }

        $verification = EnrollmentVerification::firstOrCreate(
            ['licence_id' => $licence->id],
            ['officer_id' => $user->id]
        );

        $verification->update(['escalation_reason' => $data['escalation_reason']]);
        $licence->update(['application_status' => 'escalated']);

        AuditLogger::log($user, AuditLogger::ESCALATED, $licence, [
            'escalation_reason' => $data['escalation_reason'],
        ], $request);

        return $this->successResponse(
            new EnrollmentVerificationResource($verification->fresh('officer')),
            200,
            'Application escalated.'
        );
    }

    /**
     * GET /v1/officer/enrollment/show?licence_id=X
     *
     * Returns the full application, its verification record, and its audit trail.
     * Any officer or superadmin can view (read-only).
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::with([
            'user',
            'appointment.office',
            'deliveryDetail',
            'enrollmentTransaction',
            'enrollmentVerification.officer',
        ])->find($request->licence_id);

        $licence->load($licence->detailRelationName());

        $auditTrail = AuditLog::forSubject($licence)
            ->with('user:id,first_name,last_name,email')
            ->latest()
            ->get()
            ->map(fn ($entry) => [
                'id'         => $entry->id,
                'action'     => $entry->action,
                'officer'    => $entry->user ? [
                    'id'         => $entry->user->id,
                    'first_name' => $entry->user->first_name,
                    'last_name'  => $entry->user->last_name,
                ] : null,
                'payload'    => $entry->payload,
                'created_at' => $entry->created_at,
            ]);

        return $this->successResponse([
            'licence'      => new LicenceResource($licence),
            'verification' => $licence->enrollmentVerification
                ? new EnrollmentVerificationResource($licence->enrollmentVerification)
                : null,
            'audit_trail'  => $auditTrail,
        ], 200, 'Enrollment detail retrieved.');
    }

    /**
     * GET /v1/officer/audit?licence_id=X&per_page=20
     *
     * Paginated audit log. Officers see their own entries only.
     * Superadmin sees all. Optional licence_id filter.
     */
    public function auditLog(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'licence_id' => ['sometimes', 'integer', 'exists:licences,id'],
            'per_page'   => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = AuditLog::with('user:id,first_name,last_name,email')
            ->latest();

        if ($user->role !== 'superadmin') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('licence_id')) {
            $query->where('subject_type', Licence::class)
                  ->where('subject_id', $request->licence_id);
        }

        $logs = $query->paginate($request->query('per_page', 20));

        return $this->successResponse(
            $logs->through(fn ($entry) => [
                'id'         => $entry->id,
                'action'     => $entry->action,
                'subject'    => ['type' => class_basename($entry->subject_type), 'id' => $entry->subject_id],
                'officer'    => $entry->user ? [
                    'id'         => $entry->user->id,
                    'first_name' => $entry->user->first_name,
                    'last_name'  => $entry->user->last_name,
                ] : null,
                'payload'    => $entry->payload,
                'ip_address' => $entry->ip_address,
                'created_at' => $entry->created_at,
            ])->toArray(),
            200,
            'Audit log retrieved.'
        );
    }
}
