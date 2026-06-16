<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Officer\BiometricCaptureResource;
use App\Models\BiometricCapture;
use App\Models\Licence;
use App\Services\AuditLogger;
use App\Services\ProcessDocument\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficerBiometricController extends Controller
{
    private function guardAccess(Licence $licence, Request $request): ?JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('capture_biometrics')) {
            return $this->errorResponse('You do not have permission to capture biometrics.', 403);
        }

        if ($licence->application_status !== 'verification_complete') {
            return $this->errorResponse(
                'Biometric capture is only allowed after verification is complete. Current status: ' . $licence->application_status,
                422
            );
        }

        if ($user->role !== 'superadmin' && $licence->processed_by !== $user->id) {
            return $this->errorResponse('This application is assigned to a different officer.', 403);
        }

        return null;
    }

    /**
     * GET /v1/officer/biometrics/show?licence_id=X
     *
     * Returns the current biometric capture state for a licence.
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence  = Licence::find($request->licence_id);
        $capture  = BiometricCapture::with('capturedBy')->where('licence_id', $licence->id)->first();

        return $this->successResponse(
            $capture ? new BiometricCaptureResource($capture) : null,
            200,
            $capture ? 'Biometric capture record retrieved.' : 'No biometric capture started yet.'
        );
    }

    /**
     * POST /v1/officer/biometrics/photo
     * Body (multipart): licence_id, photo (file — JPEG/PNG)
     *
     * Upload or replace the applicant's webcam photo.
     */
    public function photo(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
            'photo'      => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:5120'],
        ]);

        $licence = Licence::find($request->licence_id);

        if ($error = $this->guardAccess($licence, $request)) {
            return $error;
        }

        $capture = BiometricCapture::firstOrCreate(
            ['licence_id' => $licence->id],
            ['captured_by' => $request->user()->id]
        );

        // Delete old photo if exists
        if ($capture->photo_path) {
            Storage::disk('public')->delete($capture->photo_path);
        }

        $storageService = new FileStorageService();
        $path           = $storageService->storeUploadedFile($request->file('photo'), $capture);

        $capture->update(['photo_path' => $path]);

        return $this->successResponse(
            new BiometricCaptureResource($capture->fresh('capturedBy')),
            200,
            'Photo captured successfully.'
        );
    }

    /**
     * POST /v1/officer/biometrics/fingerprint
     * Body (JSON): licence_id, left_index_wsq (base64), right_index_wsq (base64)
     *
     * Store WSQ fingerprint data from Digital Persona device.
     * Either or both fingers can be submitted in one call.
     */
    public function fingerprint(Request $request): JsonResponse
    {
        $data = $request->validate([
            'licence_id'       => ['required', 'integer', 'exists:licences,id'],
            'left_index_wsq'   => ['sometimes', 'nullable', 'string'],
            'right_index_wsq'  => ['sometimes', 'nullable', 'string'],
        ]);

        if (! isset($data['left_index_wsq']) && ! isset($data['right_index_wsq'])) {
            return $this->errorResponse('Provide at least one fingerprint (left_index_wsq or right_index_wsq).', 422);
        }

        $licence = Licence::find($data['licence_id']);

        if ($error = $this->guardAccess($licence, $request)) {
            return $error;
        }

        $capture = BiometricCapture::firstOrCreate(
            ['licence_id' => $licence->id],
            ['captured_by' => $request->user()->id]
        );

        $update = collect($data)->only(['left_index_wsq', 'right_index_wsq'])->toArray();
        $capture->update($update);

        return $this->successResponse(
            new BiometricCaptureResource($capture->fresh('capturedBy')),
            200,
            'Fingerprint(s) saved.'
        );
    }

    /**
     * POST /v1/officer/biometrics/signature
     * Body (multipart): licence_id, signature (file — PNG/SVG)
     *  OR (JSON):       licence_id, signature_base64 (data URI)
     *
     * Upload or replace the applicant's signature from the Topaz pad.
     */
    public function signature(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id'        => ['required', 'integer', 'exists:licences,id'],
            'signature'         => ['required_without:signature_base64', 'file', 'mimes:png,svg', 'max:2048'],
            'signature_base64'  => ['required_without:signature', 'string'],
        ]);

        $licence = Licence::find($request->licence_id);

        if ($error = $this->guardAccess($licence, $request)) {
            return $error;
        }

        $capture = BiometricCapture::firstOrCreate(
            ['licence_id' => $licence->id],
            ['captured_by' => $request->user()->id]
        );

        // Delete old signature if exists
        if ($capture->signature_path) {
            Storage::disk('public')->delete($capture->signature_path);
        }

        $storageService = new FileStorageService();

        if ($request->hasFile('signature')) {
            $path = $storageService->storeUploadedFile($request->file('signature'), $capture);
        } else {
            $path = $storageService->storeBase64File($request->input('signature_base64'), $capture);
        }

        $capture->update(['signature_path' => $path]);

        return $this->successResponse(
            new BiometricCaptureResource($capture->fresh('capturedBy')),
            200,
            'Signature saved.'
        );
    }

    /**
     * POST /v1/officer/biometrics/complete
     * Body: licence_id
     *
     * Finalise biometric capture. All 4 captures must be present.
     * Sets completed_at and moves application_status → biometric_captured.
     */
    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'licence_id' => ['required', 'integer', 'exists:licences,id'],
        ]);

        $licence = Licence::find($request->licence_id);

        if ($error = $this->guardAccess($licence, $request)) {
            return $error;
        }

        $capture = BiometricCapture::where('licence_id', $licence->id)->first();

        if (! $capture || ! $capture->isComplete()) {
            $missing = [];
            if (! $capture?->photo_path)       $missing[] = 'photo';
            if (! $capture?->left_index_wsq)   $missing[] = 'left index fingerprint';
            if (! $capture?->right_index_wsq)  $missing[] = 'right index fingerprint';
            if (! $capture?->signature_path)   $missing[] = 'signature';

            return $this->errorResponse(
                'Incomplete biometric capture. Missing: ' . implode(', ', $missing) . '.',
                422
            );
        }

        if ($capture->completed_at) {
            return $this->errorResponse('Biometric capture has already been completed.', 422);
        }

        $capture->update(['completed_at' => now()]);
        $licence->update(['application_status' => 'biometric_captured']);

        AuditLogger::log($request->user(), AuditLogger::BIOMETRICS_COMPLETED, $licence, [], $request);

        return $this->successResponse(
            new BiometricCaptureResource($capture->fresh('capturedBy')),
            200,
            'Biometric capture complete. Application is ready for final approval.'
        );
    }
}
