<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    public function __construct(){
        $data = [
            ['role' => 1, 'name' => 'SAdmin'],
            ['role' => 2, 'name' => 'ADMIN'],
            ['role' => 3, 'name' => 'Parent'],
            ['role' => 4, 'name' => 'Teacher'],
            ['role' => 5, 'name' => 'Student'],
        ];
    }
    protected function respondWithToken($token)
    {
        $user='';
        if(Auth::user()->role==3){
              $user = array('name' => Auth::user()->name, 'email' => Auth::user()->email,
               'role' =>Auth::user()->role,'no_of_children'=>Auth::user()->no_of_children,'subscription_type'=>Auth::user()->subscription_type);
        }else{
             $user = array('name' => Auth::user()->name, 'email' => Auth::user()->email,'role' =>Auth::user()->role);
        }
      
        return response()->json([
            'token' =>$token,
            'userId' => Auth::user()->id,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user
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
