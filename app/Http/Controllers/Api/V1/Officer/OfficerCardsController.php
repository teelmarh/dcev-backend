<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\OfficerCardsIndexRequest;
use App\Http\Requests\Officer\OfficerCardsNotifyRequest;
use App\Models\Licence;
use App\Notifications\User\LicenceDispatchedCourierNotification;
use App\Notifications\User\LicenceReadyPickupNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerCardsController extends Controller
{
    /**
     * GET /v1/officer/cards
     *
     * Lists all approved licences (cards ready for collection or dispatch).
     * Split by delivery_method. Scoped to officer's office unless national.
     *
     */
    public function index(OfficerCardsIndexRequest $request): JsonResponse
    {
        if (! $this->canManagePrint($request)) {
            return $this->errorResponse('You do not have permission to manage print and dispatch.', 403);
        }

        $isNational = $request->user()->role === 'superadmin'
            || $request->user()->hasPermission('view_national_stats');

        $officeId = $request->user()->regional_office_id;

        if (! $isNational && ! $officeId) {
            return $this->errorResponse('You are not assigned to a regional office.', 422);
        }

        $query = Licence::with(['user', 'pickupOffice', 'deliveryDetail', 'appointment'])
            ->where('application_status', 'approved')
            ->when(
                $request->filled('delivery_method'),
                fn ($q) => $q->where('delivery_method', $request->delivery_method)
            )
            ->when(
                ! $isNational,
                fn ($q) => $q->whereHas(
                    'appointment',
                    fn ($a) => $a->where('regional_office_id', $officeId)
                )
            )
            ->latest('processed_at');

        $paginated = $query->paginate($request->query('per_page', 20));

        $summaryQuery = Licence::where('application_status', 'approved')
            ->when(
                ! $isNational,
                fn ($q) => $q->whereHas(
                    'appointment',
                    fn ($a) => $a->where('regional_office_id', $officeId)
                )
            );

        $summary = [
            'pickup'   => (clone $summaryQuery)->where('delivery_method', 'pickup')->count(),
            'delivery' => (clone $summaryQuery)->where('delivery_method', 'delivery')->count(),
        ];

        $items = $paginated->through(fn (Licence $licence) => $this->formatCard($licence));

        return $this->successResponse(
            array_merge(['summary' => $summary], $items->toArray()),
            200,
            'Cards retrieved.'
        );
    }

    /**
     * POST /v1/officer/cards/notify
     * Permission: print_management
     * Body: licence_id
     *
     * Sends a notification to the applicant:
     *   - Pickup  → email with pickup code + office address
     *   - Courier → email confirming dispatch + delivery address
     *
     * Can be called multiple times (resend). Stamps notified_at each time.
     */
    public function notify(OfficerCardsNotifyRequest $request): JsonResponse
    {
        if (! $this->canManagePrint($request)) {
            return $this->errorResponse('You do not have permission to manage print and dispatch.', 403);
        }

        $licence = Licence::with(['user', 'pickupOffice', 'deliveryDetail', 'appointment'])
            ->find($request->licence_id);

        if ($licence->application_status !== 'approved') {
            return $this->errorResponse('Notifications can only be sent for approved licences.', 422);
        }

        if (! $licence->delivery_method) {
            return $this->errorResponse('This licence has no delivery method set.', 422);
        }

        $applicant = $licence->user;

        if ($licence->delivery_method === 'pickup') {
            if (! $licence->pickup_code) {
                return $this->errorResponse('This licence has no pickup code. Re-processing may be required.', 422);
            }
            $applicant->notify(new LicenceReadyPickupNotification($licence));
        } else {
            $applicant->notify(new LicenceDispatchedCourierNotification($licence));
        }

        $licence->update(['notified_at' => now()]);

        return $this->successResponse(
            $this->formatCard($licence->fresh(['user', 'pickupOffice', 'deliveryDetail', 'appointment'])),
            200,
            'Notification sent successfully.'
        );
    }

    // -----------------------------------------------------------------------

    private function canManagePrint(Request $request): bool
    {
        $user = $request->user();
        return $user->role === 'superadmin' || $user->hasPermission('print_management');
    }

    private function formatCard(Licence $licence): array
    {
        $card = [
            'licence_id'      => $licence->id,
            'licence_number'  => $licence->licence_number,
            'type'            => $licence->type,
            'delivery_method' => $licence->delivery_method,
            'processed_at'    => $licence->processed_at?->toDateTimeString(),
            'notified_at'     => $licence->notified_at?->toDateTimeString(),
            'applicant'       => $licence->user ? [
                'id'         => $licence->user->id,
                'first_name' => $licence->user->first_name,
                'last_name'  => $licence->user->last_name,
                'email'      => $licence->user->email,
                'phone'      => $licence->user->phone,
            ] : null,
        ];

        if ($licence->delivery_method === 'pickup') {
            $card['pickup_code'] = $licence->pickup_code;
            $card['pickup_office'] = $licence->pickupOffice ? [
                'id'      => $licence->pickupOffice->id,
                'name'    => $licence->pickupOffice->name,
                'address' => $licence->pickupOffice->address,
                'city'    => $licence->pickupOffice->city,
                'state'   => $licence->pickupOffice->state,
                'phone'   => $licence->pickupOffice->phone ?? null,
            ] : null;
        } else {
            $detail = $licence->deliveryDetail;
            $card['courier'] = $detail ? [
                'recipient_name'       => $detail->recipient_name,
                'recipient_phone'      => $detail->recipient_phone,
                'address_line'         => $detail->address_line,
                'city'                 => $detail->city,
                'state'                => $detail->state,
                'lga'                  => $detail->lga,
                'postal_code'          => $detail->postal_code,
                'courier_instructions' => $detail->courier_instructions,
            ] : null;
        }

        return $card;
    }
}
