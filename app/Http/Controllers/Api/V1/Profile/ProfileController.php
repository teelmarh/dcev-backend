<?php

namespace App\Http\Controllers\Api\V1\Profile;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Users\UserResource;
use App\Services\ProcessDocument\ProcessMediaService;
use App\Http\Requests\User\Profile\UpdateProfileFormRequest;

class ProfileController extends Controller
{
    /**
     * Get User profile details
     *
     * Get User profile details
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $memberSince = Carbon::parse($user->created_at)->format('F Y');

        $media = $user->media()->first();
        $imageUrl = $media ? Storage::url($media->file_path) : null;

        $data = [
            'user' => new UserResource($user),
            'member_since' => $memberSince,
            'status' => (bool) $user->is_active,
            'image_url' => $imageUrl,
        ];
        
        return $this->dataResponse($data, 'User data fetched successfully', true, 200);
    }

    /**
     * Update User profile
     *
     * Update User profile
     */
    public function store(UpdateProfileFormRequest $request)
    {
        $user = $request->user();
        
   

        $updateData = $request->validated();

        if ($request->image) {
            $imagePath = app(ProcessMediaService::class)->processImage($request->image, $user);
            $updateData['image_url'] = \Storage::url($imagePath);
        }

        unset($updateData['image']);
        $user->update($updateData);
        
        return $this->dataResponse($user, 'User data Updated successfully', true, 200);
    }
}
