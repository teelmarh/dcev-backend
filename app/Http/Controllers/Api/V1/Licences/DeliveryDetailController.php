<?php

namespace App\Http\Controllers\Api\V1\Licences;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\StoreDeliveryDetailRequest;
use App\Http\Resources\Licences\DeliveryDetailResource;
use App\Models\Licence;
use App\Traits\Api\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryDetailController extends Controller
{
    use ApiResponder;

    /**
     * POST /v1/licences/delivery
     */
    public function store(StoreDeliveryDetailRequest $request): JsonResponse
    {
        $licence = Licence::findOrFail($request->validated('licence_id'));

        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($licence->delivery_method !== 'delivery') {
            return $this->errorResponse('This licence is set for pickup, not delivery.', 422);
        }

        $detail = $licence->deliveryDetail()->updateOrCreate(
            ['licence_id' => $licence->id],
            $request->safe()->except('licence_id')
        );

        return $this->dataResponse(
            new DeliveryDetailResource($detail),
            'Delivery details saved.',
            true,
            201
        );
    }

    /**
     * GET /v1/licences/delivery
     */
    public function show(Request $request): JsonResponse
    {
        $licence = Licence::findOrFail($request->integer('licence_id'));

        if ($licence->user_id !== $request->user()->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $detail = $licence->deliveryDetail;

        if (! $detail) {
            return $this->errorResponse('No delivery details found.', 404);
        }

        return $this->dataResponse(new DeliveryDetailResource($detail), 'Delivery details retrieved.', true, 200);
    }
}
