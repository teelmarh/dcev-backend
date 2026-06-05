<?php

namespace App\Http\Controllers\Api\V1\Licences\Fcl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Fcl\StoreCabinCrewLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FclCabinCrewController extends Controller
{
    public function store(StoreCabinCrewLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'fcl',
                'type'             => 'cabin_crew',
                'application_type' => 'data_capture',
            ]));

            $licence->cabinCrewDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['operator', 'aircraft_types', 'valid_from', 'valid_to'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('cabinCrewDetail')),
            'Cabin crew licence record saved.',
            true,
            201
        );
    }
}
