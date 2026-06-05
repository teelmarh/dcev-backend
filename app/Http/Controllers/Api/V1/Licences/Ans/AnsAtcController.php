<?php

namespace App\Http\Controllers\Api\V1\Licences\Ans;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Ans\StoreAtcLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnsAtcController extends Controller
{
    public function store(StoreAtcLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'ans',
                'type'             => 'atc',
                'application_type' => 'data_capture',
            ]));

            $licence->atcDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['ratings', 'unit', 'endorsements'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('atcDetail')),
            'ATC licence record saved.',
            201
        );
    }
}
