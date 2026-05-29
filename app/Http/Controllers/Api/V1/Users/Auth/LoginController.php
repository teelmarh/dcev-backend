<?php
namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Models\User;
use App\Models\LoginHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Users\UserResource;
use App\Http\Requests\User\Auth\LoginFormRequest;

class LoginController extends Controller
{

    public function store(LoginFormRequest $request)
    {
        //there is need to work on the flow where incomplete registerations can be completed
        
        $user = User::where(['email' => $request['email']])->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return $this->errorResponse('Email not verified', 401);
        }
    
        $token = $user->createToken('auth_token', ['role:user'])->plainTextToken;
        

        $response = [
            'user' => new UserResource($user),
            'access_token' => $token,
        ];

        return $this->successResponse($response, 200);
    }


}


