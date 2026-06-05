<?php

namespace App\Http\Controllers\Api\V1\Licences\Fcl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Fcl\StoreFlightDispatchLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FclFlightDispatchController extends Controller
{
    public function store(StoreFlightDispatchLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'fcl',
                'type'             => 'flight_dispatch',
                'application_type' => 'data_capture',
            ]));

            $licence->flightDispatchDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['ratings'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('flightDispatchDetail')),
            'Flight dispatch licence record saved.',
            201
        );
    }
}
