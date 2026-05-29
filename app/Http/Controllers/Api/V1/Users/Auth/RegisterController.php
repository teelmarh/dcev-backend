<?php

namespace App\Http\Controllers\Api\V1\Users\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\RegisterUserRequest;

class RegisterController extends Controller
{
    public function store(RegisterUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = User::create([
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'message'      => 'User created successfully.',
            'success'      => true,
            'data'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }
}