<?php

namespace App\Http\Controllers\Api\V1\Licences;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\StoreDeliveryDetailRequest;
use App\Http\Resources\Licences\DeliveryDetailResource;
use App\Models\Licence;
use App\Traits\Api\ApiResponder;
use Illuminate\Http\JsonResponse;

class DeliveryDetailController extends Controller
{
    use ApiResponder;

    /**
     * POST /v1/licences/{licence}/delivery
     * Store or update delivery details for a licence.
     */
    public function store(StoreDeliveryDetailRequest $request, Licence $licence): JsonResponse
    {
        if ($licence->user_id !== $request->user()->id) {
            return $this->dataResponse(null, 'Forbidden.', false, 403);
        }

        if ($licence->delivery_method !== 'delivery') {
            return $this->dataResponse(null, 'This licence is set for pickup, not delivery.', false, 422);
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
     * GET /v1/licences/{licence}/delivery
     */
    public function show(Licence $licence): JsonResponse
    {
        if ($licence->user_id !== request()->user()->id) {
            return $this->dataResponse(null, 'Forbidden.', false, 403);
        }

        $detail = $licence->deliveryDetail;

        if (! $detail) {
            return $this->dataResponse(null, 'No delivery details found.', false, 404);
        }

        return $this->dataResponse(new DeliveryDetailResource($detail), 'Delivery details retrieved.', true, 200);
    }
}
