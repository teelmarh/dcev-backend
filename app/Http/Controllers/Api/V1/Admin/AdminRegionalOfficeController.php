<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Region\StoreRegionRequest;
use App\Http\Requests\Admin\Region\UpdateRegionRequest;
use App\Models\Appointment;
use App\Models\RegionalOffice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRegionalOfficeController extends Controller
{
    /** GET /v1/admin/regions */
    public function index(Request $request): JsonResponse
    {
        $regions = RegionalOffice::query()
            ->when($request->query('active') !== null, function ($q) use ($request) {
                $q->where('active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($r) => $this->formatRegion($r));

        return $this->successResponse($regions, 200, 'Regions retrieved.');
    }

    /** GET /v1/admin/regions/show?region_id=X */
    public function show(Request $request): JsonResponse
    {
        $request->validate(['region_id' => ['required', 'integer', 'exists:regional_offices,id']]);

        $region = RegionalOffice::find($request->region_id);

        return $this->successResponse($this->formatRegion($region), 200, 'Region retrieved.');
    }

    /** POST /v1/admin/regions */
    public function store(StoreRegionRequest $request): JsonResponse
    {
        $data         = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $region = RegionalOffice::create($data);

        return $this->successResponse($this->formatRegion($region), 201, 'Region created.');
    }

    /** PATCH /v1/admin/regions */
    public function update(UpdateRegionRequest $request): JsonResponse
    {
        $region = RegionalOffice::find($request->region_id);

        $data = collect($request->validated())->except('region_id')->toArray();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $region->update($data);

        return $this->successResponse($this->formatRegion($region->fresh()), 200, 'Region updated.');
    }

    /** DELETE /v1/admin/regions */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['region_id' => ['required', 'integer', 'exists:regional_offices,id']]);

        $region = RegionalOffice::find($request->region_id);

        if ($region->appointments()->whereNotIn('status', ['cancelled', 'completed'])->exists()) {
            return $this->errorResponse('Cannot delete a region with active or pending appointments.', 422);
        }

        $region->delete();

        return $this->showMessage('Region deleted.', 200);
    }

    /**
     * PATCH /v1/admin/regions/capacity
     * Quickly update only the daily_capacity (and optionally working hours) of a region.
     * Body: region_id, daily_capacity, [working_hours_start, working_hours_end]
     */
    public function updateCapacity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'region_id'           => ['required', 'integer', 'exists:regional_offices,id'],
            'daily_capacity'      => ['required', 'integer', 'min:1', 'max:9999'],
            'working_hours_start' => ['sometimes', 'date_format:H:i'],
            'working_hours_end'   => ['sometimes', 'date_format:H:i', 'after:working_hours_start'],
        ]);

        $region = RegionalOffice::find($data['region_id']);
        $region->update(collect($data)->except('region_id')->toArray());

        return $this->successResponse([
            'id'                  => $region->id,
            'name'                => $region->name,
            'daily_capacity'      => $region->daily_capacity,
            'working_hours_start' => $region->working_hours_start,
            'working_hours_end'   => $region->working_hours_end,
        ], 200, 'Capacity updated.');
    }

    private function formatRegion(RegionalOffice $region): array
    {
        return [
            'id'                  => $region->id,
            'name'                => $region->name,
            'slug'                => $region->slug,
            'state'               => $region->state,
            'city'                => $region->city,
            'address'             => $region->address,
            'phone'               => $region->phone,
            'email'               => $region->email,
            'daily_capacity'      => $region->daily_capacity,
            'working_hours_start' => $region->working_hours_start,
            'working_hours_end'   => $region->working_hours_end,
            'active'              => $region->active,
            'created_at'          => $region->created_at,
        ];
    }
}
