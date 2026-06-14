<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Models\Licence;
use App\Models\LicenceDeliveryDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerDeliveryController extends Controller
{
    /**
     * GET /v1/officer/delivery/dispatch
     * Permission: manage_delivery
     *
     * Returns the full dispatch list — all licences with an approved status
     * that have a delivery_method set (both pickup and courier delivery).
     *
     * Filters: delivery_method (pickup|delivery), pickup_office_id, state, date_from, date_to
     */
    public function dispatch(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage_delivery') && $request->user()->role !== 'superadmin') {
            return $this->errorResponse('You do not have permission to manage delivery.', 403);
        }

        $request->validate([
            'delivery_method'  => ['sometimes', 'string', 'in:pickup,delivery'],
            'pickup_office_id' => ['sometimes', 'integer', 'exists:regional_offices,id'],
            'state'            => ['sometimes', 'string'],
            'date_from'        => ['sometimes', 'date'],
            'date_to'          => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page'         => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Licence::with(['user', 'deliveryDetail', 'pickupOffice', 'processedBy'])
            ->whereNotNull('delivery_method')
            // Only show licences that have been processed (approved/rejected etc.) — adjust status as needed
            ->whereNotNull('processed_at')
            ->when($request->filled('delivery_method'), fn ($q) => $q->where('delivery_method', $request->delivery_method))
            ->when($request->filled('pickup_office_id'), fn ($q) => $q->where('pickup_office_id', $request->pickup_office_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('processed_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('processed_at', '<=', $request->date_to))
            ->latest('processed_at');

        // State filter applies to courier delivery addresses only
        if ($request->filled('state')) {
            $query->whereHas('deliveryDetail', fn ($q) => $q->where('state', $request->state));
        }

        $licences = $query->paginate($request->query('per_page', 20));

        $items = $licences->through(fn (Licence $licence) => $this->formatDispatchItem($licence));

        return $this->successResponse($items->toArray(), 200, 'Dispatch list retrieved.');
    }

    /**
     * GET /v1/officer/delivery/show?licence_id=X
     * Permission: manage_delivery
     *
     * Returns full delivery detail for a single licence.
     */
    public function show(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage_delivery') && $request->user()->role !== 'superadmin') {
            return $this->errorResponse('You do not have permission to manage delivery.', 403);
        }

        $request->validate(['licence_id' => ['required', 'integer', 'exists:licences,id']]);

        $licence = Licence::with(['user', 'deliveryDetail', 'pickupOffice', 'processedBy'])
            ->find($request->licence_id);

        if (! $licence->delivery_method) {
            return $this->errorResponse('This licence has no delivery information.', 404);
        }

        return $this->successResponse($this->formatDispatchItem($licence), 200, 'Delivery detail retrieved.');
    }

    private function formatDispatchItem(Licence $licence): array
    {
        $base = [
            'licence_id'      => $licence->id,
            'licence_number'  => $licence->licence_number,
            'type'            => $licence->type,
            'status'          => $licence->status,
            'delivery_method' => $licence->delivery_method,
            'processed_at'    => $licence->processed_at?->toDateTimeString(),
            'processed_by'    => $licence->processedBy ? [
                'id'         => $licence->processedBy->id,
                'first_name' => $licence->processedBy->first_name,
                'last_name'  => $licence->processedBy->last_name,
            ] : null,
            'applicant'       => $licence->user ? [
                'id'         => $licence->user->id,
                'first_name' => $licence->user->first_name,
                'last_name'  => $licence->user->last_name,
                'email'      => $licence->user->email,
                'phone'      => $licence->user->phone,
            ] : null,
        ];

        if ($licence->delivery_method === 'pickup') {
            $base['pickup'] = $licence->pickupOffice ? [
                'office_id' => $licence->pickupOffice->id,
                'name'      => $licence->pickupOffice->name,
                'address'   => $licence->pickupOffice->address,
                'city'      => $licence->pickupOffice->city,
                'state'     => $licence->pickupOffice->state,
                'phone'     => $licence->pickupOffice->phone,
            ] : null;
        } else {
            $detail          = $licence->deliveryDetail;
            $base['courier'] = $detail ? [
                'recipient_name'       => $detail->recipient_name,
                'recipient_phone'      => $detail->recipient_phone,
                'address_line'         => $detail->address_line,
                'city'                 => $detail->city,
                'state'                => $detail->state,
                'lga'                  => $detail->lga,
                'postal_code'          => $detail->postal_code,
                'courier_instructions' => $detail->courier_instructions,
                'delivery_paid'        => $detail->isPaid(),
            ] : null;
        }

        return $base;
    }
}
