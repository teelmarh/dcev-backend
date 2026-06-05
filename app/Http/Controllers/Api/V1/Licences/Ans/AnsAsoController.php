<?php

namespace App\Http\Controllers\Api\V1\Licences\Ans;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Ans\StoreAsoLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnsAsoController extends Controller
{
    public function store(StoreAsoLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'ans',
                'type'             => 'aso',
                'application_type' => 'data_capture',
            ]));

            $licence->asoDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['aerodrome_category'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('asoDetail')),
            'ASO licence record saved.',
            true,
            201
        );
    }
}
