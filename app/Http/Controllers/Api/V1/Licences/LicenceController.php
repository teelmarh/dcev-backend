<?php

namespace App\Http\Controllers\Api\V1\Licences;

use App\Http\Controllers\Controller;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenceController extends Controller
{
    /**
     * Return all licences belonging to the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $licences = $request->user()
            ->licences()
            ->latest()
            ->paginate(20);

        $licences->each(function (Licence $licence) {
            $licence->load($licence->detailRelationName());
        });

        return LicenceResource::collection($licences);
    }

    /**
     * Return a single licence. Restricted to the owner.
     */
    public function show(Request $request, Licence $licence): JsonResponse
    {
        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('This licence does not belong to your account.', 403);
        }

        $licence->load($licence->detailRelationName());

        return $this->dataResponse(new LicenceResource($licence), 'Licence retrieved.', true, 200);
    }

    /**
     * Stream the licence PDF document. Restricted to the owner.
     */
    public function downloadDocument(Request $request, Licence $licence): StreamedResponse|JsonResponse
    {
        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('This licence does not belong to your account.', 403);
        }

        if (!$licence->licence_document_path || !Storage::disk('local')->exists($licence->licence_document_path)) {
            return $this->errorResponse('No document uploaded for this licence.', 404);
        }

        return Storage::disk('local')->download($licence->licence_document_path, 'licence-document.pdf');
    }

    /**
     * Stream the passport photo. Restricted to the owner.
     */
    public function downloadPhoto(Request $request, Licence $licence): StreamedResponse|JsonResponse
    {
        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('This licence does not belong to your account.', 403);
        }

        if (!$licence->passport_photo_path || !Storage::disk('local')->exists($licence->passport_photo_path)) {
            return $this->errorResponse('No passport photo uploaded for this licence.', 404);
        }

        $extension = pathinfo($licence->passport_photo_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($licence->passport_photo_path, "passport-photo.{$extension}");
    }
}
