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
        # check user role for appending kidz data if role is parent /school
        if(Auth::user()->role==3 ||Auth::user()->role==4){
              $user = array('name' => Auth::user()->name, 
              'email' => Auth::user()->email,
              'id' => Auth::user()->id,
               'role' =>Auth::user()->role,
               'country_code' =>Auth::user()->country_code,
               'no_of_children'=>Auth::user()->no_of_children,
               'subscription_type'=>Auth::user()->subscription_type,'purchaed_date'=>Auth::user()->purchased_datetime);
            $kids_data=DB::table('users')->where('parent_id',Auth::user()->id)->select('id','name','email','role')->get();
        }#role iis student
        else{
             $user = array('name' => Auth::user()->name,
             'email' => Auth::user()->email,
              'id' => Auth::user()->id,
             'role' =>Auth::user()->role,
               'country_code' =>Auth::user()->country_code
            );
            
        }
        #if role is parent/school add kidz data
      if(Auth::user()->role==3 || Auth::user()->role==4){
        return response()->json([
            'status'=>true,
            'token' =>$token,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user,            
            'kids_data'=>$kids_data
        ], 200);
      }else{
          #logged in as student
          return response()->json([
            'status'=>true,
            'token' =>$token,
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
