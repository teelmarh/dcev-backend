<?php

namespace App\Http\Controllers\Api\V1\Licences\Amel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Amel\StoreAmeLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AmelAmeController extends Controller
{
    public function store(StoreAmeLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'amel',
                'type'             => 'ame',
                'application_type' => 'data_capture',
            ]));

            $licence->ameDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only(['categories', 'aircraft_types', 'scope_of_work'])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('ameDetail')),
            'AME licence record saved.',
            201
        );
    }
}
