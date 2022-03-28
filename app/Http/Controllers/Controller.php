<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use DB;

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
        $user='';$kids_data=[];
        if(Auth::user()->role==3){
              $user = array('name' => Auth::user()->name, 
              'email' => Auth::user()->email,
               'role' =>Auth::user()->role,
               'no_of_children'=>Auth::user()->no_of_children,
               'subscription_type'=>Auth::user()->subscription_type);
            $kids_data=DB::table('users')->where('parent_id',Auth::user()->id)->select('id','name','email','role')->get();
        }else{
             $user = array('name' => Auth::user()->name,
             'email' => Auth::user()->email,
             'role' =>Auth::user()->role);
             $kids_data=DB::table('users')->where('parent_id',Auth::user()->id)->select('id','name','email','role')->get();
        }
      if(Auth::user()->role==3 || Auth::user()->role==4){
        return response()->json([
            'status'=>true,
            'token' =>$token,
            'userId' => Auth::user()->id,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user,            
            'kids_data'=>$kids_data
        ], 200);
      }else{
          return response()->json([
            'status'=>true,
            'token' =>$token,
            'userId' => Auth::user()->id,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user
        ], 200);
      }
        
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
