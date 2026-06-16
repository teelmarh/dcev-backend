<?php
namespace App\Http\Controllers\Api\V1\Users\Auth;


use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        AuditLogger::log($user, AuditLogger::LOGOUT, $user, [], $request);

        $user->tokens()->delete();

        return $this->errorResponse('Logout Success', 200);
    }
}

