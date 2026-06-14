<?php

namespace App\Http\Controllers\Api\V1\Officer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Licences\LicenceResource;
use App\Models\Licence;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerHandledApplicationsController extends Controller
{
    /**
     * GET /v1/officer/handled-applications
     * Permission: view_handled_applications
     *
     * An officer sees their own handled applications.
     * Superadmin or an officer with the permission can pass ?officer_id=X to view another officer's work.
     *
     * Filters: status, type, date_from, date_to, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'superadmin' && ! $user->hasPermission('view_handled_applications')) {
            return $this->errorResponse('You do not have permission to view handled applications.', 403);
        }

        $request->validate([
            'officer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'status'     => ['sometimes', 'string'],
            'type'       => ['sometimes', 'string'],
            'date_from'  => ['sometimes', 'date'],
            'date_to'    => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page'   => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        // Determine whose handled applications to show
        $canViewOthers = $user->role === 'superadmin';
        if ($request->filled('officer_id') && ! $canViewOthers) {
            return $this->errorResponse('You do not have permission to view another officer\'s handled applications.', 403);
        }

        $officerId = $request->filled('officer_id') ? (int) $request->officer_id : $user->id;

        $query = Licence::with(['user', 'processedBy'])
            ->where('processed_by', $officerId)
            ->whereNotNull('processed_at')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('processed_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('processed_at', '<=', $request->date_to))
            ->latest('processed_at');

        $licences = $query->paginate($request->query('per_page', 20));

        // Load type-specific detail per licence
        $licences->each(fn ($l) => $l->load($l->detailRelationName()));

        return $this->successResponse(
            LicenceResource::collection($licences)->response()->getData(true),
            200,
            'Handled applications retrieved.'
        );
    }
}
