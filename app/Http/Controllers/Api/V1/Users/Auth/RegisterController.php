<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;
use App\Services\User\MembershipFeeService;
use App\Http\Requests\User\Auth\RegisterUserRequest;
use App\Services\ProcessDocument\ProcessMediaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
;


class RegisterController extends Controller
{
    public function store(RegisterUserRequest $request)
    {
      echo $request->disabled;
        $registrationFee = (new membershipFeeService)->getRegistrationFee($request->dob, $request->disabled??false);

        $membershipId = $this->generateMembershipID($request->state_chapter);

        try {
            $user = $this->createUser($request, $membershipId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create user.'], 500);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'registration_fee' => $registrationFee,
            'message' => "User Created Successfully",
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
    /**
     * Generate a unique membership ID.
     *
     * @param string $stateChapter
     * @return string
     */
    protected function generateMembershipID(string $stateChapter): string
    {
        $stateCode = strtoupper(substr($stateChapter, 0, 3));
        
        DB::beginTransaction();
        
        try {
            $latestUser = User::where('state_chapter', $stateChapter)
                            ->lockForUpdate()
                            ->latest('id')
                            ->first();
    
            $nextNumber = 1;
            if ($latestUser && $latestUser->membership_id) {
                preg_match('/\d+$/', $latestUser->membership_id, $matches);
                $nextNumber = $matches ? ((int)$matches[0] + 1) : 1;
            }
    
            $newMembershipId = "ADC/{$stateCode}/" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            DB::commit();
            
            return $newMembershipId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a new user.
     *
     * @param registerUserRequest $request
     * @param string $membershipId
     * @return User
     */
    protected function createUser(registerUserRequest $request, string $membershipId): User
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'state_chapter' => $request->state_chapter,
            'dob' => $request->dob,
            'state_of_origin' => $request->state_of_origin,
            'lga' => $request->lga,
            'ward' => $request->ward,
            'gender' => $request->gender,
            'occupation' => $request->occupation,
            'password' => bcrypt($request->password),
            'membership_id' => $membershipId,
            'image_url' => $request->image_url,
            'phone' => $request->phone,
            'disabled' => $request->disabled ?? false,
        ]);
    
        if ($request->image) {
            $filePath = (new ProcessMediaService)->processImage($request->image, $user);
            $image_url = Storage::url($filePath);
    
            $user->update(['image_url' => $image_url]);
        }
    
        return $user;
    }
}