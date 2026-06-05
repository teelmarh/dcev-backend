<?php

namespace App\Http\Controllers\Api\V1\Licences\Ans;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Ans\StoreAtsepLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnsAtsepController extends Controller
{
    public function store(StoreAtsepLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'ans',
                'type'             => 'atsep',
                'application_type' => 'data_capture',
            ]));

            $licence->atsepDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['ratings'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('atsepDetail')),
            'ATSEP licence record saved.',
            201
        );
    }
}
