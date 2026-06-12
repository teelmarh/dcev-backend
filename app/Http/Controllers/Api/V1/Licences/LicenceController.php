<?php

namespace App\Http\Controllers\Api\V1\Licences;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\ShowLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LicenceController extends Controller
{
    /**
     * Return all licences belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $licences = $request->user()
            ->licences()
            ->latest()
            ->paginate(20);

        $licences->each(function (Licence $licence) {
            $licence->load($licence->detailRelationName(), 'appointment.office');
        });

        return $this->dataResponse(
            LicenceResource::collection($licences),
            'Licences retrieved.',
            true,
            200
        );
    }

    /**
     * Return a single licence. Restricted to the owner.
     */
    public function show(ShowLicenceRequest $request): JsonResponse
    {
        $licence = Licence::find($request->validated('licence_id'));

        if (! $licence) {
            return $this->errorResponse('Licence not found.', 404);
        }

        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('This licence does not belong to your account.', 403);
        }

        $licence->load($licence->detailRelationName(), 'appointment.office');

        return $this->dataResponse(new LicenceResource($licence), 'Licence retrieved.', true, 200);
    }
}
