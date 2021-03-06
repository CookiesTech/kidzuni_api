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
            ['role' => 4, 'name' => 'School'],
            ['role' => 5, 'name' => 'Student'],
            ['role' => 6, 'name' => 'Teacher'],
        ];
    }
    protected function respondWithToken($token,$user1)
    {
        $kids_data=[];$user='';
        # check user role for appending kidz data if role is parent /school
        if($user1->role==3 || $user1->role==4){
              $user = array('name' => $user1->name, 
              'email' => $user1->email,
              'id' => $user1->id,
               'role' =>$user1->role,
               'country_code' =>$user1->country_code,
               'no_of_children'=>$user1->no_of_children,
               'subscription_type'=>$user1->subscription_type,
               'purchaed_date'=>$user1->purchased_datetime,
               'expiry_date'=>$user1->expiry_date
              
              );
            //$kids_data=DB::table('users')->where('parent_id',Auth::user()->id)->select('id','name','email','role','username','password')->get();
        }#role iis student
        else{
             $user = array('name' => $user1->name,
              'username' => $user1->username,
              'id' => $user1->id,
              'role' =>$user1->role,
              'country_code' =>$user1->country_code
            );
            
        }
        #if role is parent/school add kidz data
      if($user1->role==3 || $user1->role==4){
        return response()->json([
            'status'=>true,
            'token' =>$token,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user,            
            //'kids_data'=>$kids_data
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

    protected function respondWithToken1($token)
    {
      
       $user = array('name' => Auth::user()->name, 
              'email' => Auth::user()->email,
              'id' => Auth::user()->id,
               'role' =>Auth::user()->role);
               
        return response()->json([
            'status'=>true,
            'token' =>$token,
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
