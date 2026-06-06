<?php

namespace App\Http\Controllers\Api\V1\Licences\Fcl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Licences\Fcl\StorePilotLicenceRequest;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FclPilotController extends Controller
{
    public function store(StorePilotLicenceRequest $request): JsonResponse
    {
        $licence = DB::transaction(function () use ($request) {
            $licence = Licence::create(array_merge($request->baseLicenceData(), [
                'user_id'          => $request->user()->id,
                'family'           => 'fcl',
                'type'             => 'pilot',
                'application_type' => 'data_capture',
            ]));

            $licence->pilotDetail()->create(array_merge(
                $request->sharedDetailData(),
                $request->safe()->only([
                    'ratings', 'aircraft_categories', 'endorsements',
                    'skill_test_pic_time',
                    'military_service', 'military_date_rated', 'military_rank_grade',
                ])
            ));

            return $licence;
        });

        return $this->dataResponse(
            new LicenceResource($licence->load('pilotDetail')),
            'Pilot licence record saved.',
            true,
            201
        );
    }
}
