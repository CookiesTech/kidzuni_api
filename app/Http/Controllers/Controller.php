<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
     /**
     * @OA\Info(
     *    title="Student  ApplicationAPI",
     *    version="1.0.0",
     * )
     */
    protected function respondWithToken($token)
    {
        $user = array('name' => Auth::user()->name, 'email' => Auth::user()->email, 'role' => 'SA');
        return response()->json([
            'token' => $token,
            'userId' => Auth::user()->id,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' => $user
        ], 200);
    }

    protected function formatErrorResponse(Validator $validator)
    {
        return response()->json(
            [
                'status' => false,
                'message' => collect($validator->errors())->map(function ($message) {
                    return $message[0];
                }),
            ],
            200
        );
    }
}
