<?php

namespace App\Http\Controllers\Api\V1\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Users\UserResource;
use App\Services\ProcessDocument\ProcessMediaService;
use App\Http\Requests\User\Profile\UpdateProfileFormRequest;

class ProfileController extends Controller
{
    /**
     * Get authenticated user's profile.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $data = [
            'user'         => new UserResource($user),
            'member_since' => $user->created_at->format('F Y'),
            'photo_url'    => $user->photo ? Storage::disk('public')->url($user->photo) : null,
        ];

        return $this->dataResponse($data, 'User data fetched successfully', true, 200);
    }

    /**
     * Update authenticated user's profile.
     * Accepts photo as multipart file (image_file) or base64 data URI (image).
     */
    public function store(UpdateProfileFormRequest $request)
    {
        $user       = $request->user();
        $updateData = collect($request->validated())
            ->except(['image', 'image_file'])
            ->filter(fn ($v) => ! is_null($v))
            ->toArray();

        $imageInput = $request->file('image_file') ?? $request->input('image');

        if ($imageInput) {
            $path                = app(ProcessMediaService::class)->processImage($imageInput, $user);
            $updateData['photo'] = $path;
        }

        $user->update($updateData);

        return $this->dataResponse(
            new UserResource($user->fresh()),
            'Profile updated successfully',
            true,
            200
        );
    }
}
