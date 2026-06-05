<?php

namespace App\Http\Controllers\Api\V1\Licences;

use App\Http\Controllers\Controller;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

        // Eager-load the appropriate detail relation for each licence
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

        return $this->dataResponse(new LicenceResource($licence));
    }
}
