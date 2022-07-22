<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use DB;
use Firebase\JWT\JWT;
use Validator;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',[
            'except'=>['login','register','admin_login','forgot_password','update_password','teacher_login'],
        ]);
    }
    protected function create_token($role,$id,$expiry)
    {
       
        $total_time = $expiry;
        $payload = [

            'iss' => "lumen-jwt", // Issuer of the token
            'user_id' => $id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() +  $total_time, // Expiration time,
            'role'=>$role
        ];


        return JWT::encode($payload, env('JWT_SECRET'));
    }
    public function register(Request $request)
    {
        //validate incoming request 
         $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required',
            'type' => 'required',
            'package_for' => 'required',
            'no_of_children' => 'required',
        ]);
       

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }

        try {
           
            $role='';
             if($request->input('package_for')=='school'){
                    $role=4;
                }else{
                    $role=3;
                }
            $expiry_date='';
            if($request->input('type')=='annual'){
                $date =date('Y-m-d H:i:s');
                $expiry_date = date("Y-m-d H:i:s", strtotime ( '+12 month' , strtotime ( $date ) )) ;
               
            }
            #plan type in month
            else{
                 $date =date('Y-m-d H:i:s');
                $expiry_date = date("Y-m-d H:i:s", strtotime ( '+1 month' , strtotime ( $date ) )) ;
            }
            DB::table('users')->insert([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' =>$request->input('password'),
                'phone_no'=>$request->input('phone_no'),
                'no_of_children'=>$request->input('no_of_children'),
                'subscription_type'=>$request->input('type'),
                'package_for'=>$request->input('package_for'),
                'country_code'=>$request->input('country_code'),
                'price'=>$request->post('price'),
                 'role'=>$role,                
                'purchased_datetime'=>date('Y-m-d H:i:s'),
                'expiry_date'=>$expiry_date,
                'address'=>$request->input('address'),
                'school_name'=>$request->input('school_name')

            ]);


            //return successful response
            return response()->json(['status' => true, 'message' => 'User Registration Successfully'], 200);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['status' => false, 'message' =>$e], 409);
        }
    }
    public function login(Request $request)
    {
        //validate incoming request      
        $input = $request->all();
        $this->validate($request, [

            'username' => 'required',

            'password' => 'required',

        ]);

  

        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if($fieldType=='username'){
             $user = DB::table('users')->where('username',$request->username)
                ->wherePassword($request->password)
                ->first();
        }else{
           $user = DB::table('users')->whereEmail($request->username)
                ->wherePassword($request->password)
                ->first();

           
        }
        
        if($user)
        {   
                $current_datetime=date('Y-m-d H:i:s');               
                $date =$user->expiry_date;
                $expiry_date =date("Y-m-d H:i:s",strtotime($user->expiry_date));
                #check plan expired or not 
                $diff= strtotime($expiry_date)-strtotime($current_datetime);  
                    
                if($diff >0){
                    
                    $token = $this->create_token($user->role,$user->id, env('SESSION_TOKEN_EXPIRY'));
                    return $this->respondWithToken($token,$user);
                }#plan expired
                else{
                    return response()->json(['status'=>false,'message' => 'Plan Expired'], 200);
                }
           

        }
        else{

            return response()->json(['status'=>false,'message' => 'Unauthorized'], 200);

        }
       
    }



    public function admin_login(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $user = DB::table('teachers')->whereEmail($request->email)
                ->wherePassword($request->password)
                ->where('role',1)
                ->first();

        if (!$user) {
            return response()->json(['status'=>false,'message' => 'Unauthorized'], 200);
        }#to only admin can login
        else {

            $token = $this->create_token($user->role,$user->id, env('SESSION_TOKEN_EXPIRY'));
            return $this->respondWithToken1($token,$user);
        }
    }


     public function teacher_login(Request $request)
    {
        //validate incoming request      
        $input = $request->all();
        $this->validate($request, [

            'email' => 'required',

            'password' => 'required',

        ]);
        $user = DB::table('teachers')->whereEmail($request->email)
                ->wherePassword($request->password)
                ->where('status',1)
                ->first();
        if($user)
        {        
            $token = $this->create_token($user->role,$user->id, env('SESSION_TOKEN_EXPIRY'));
            $user = array('name' => $user->first_name, 
              'email' => $user->email,
              'id' => $user->id,
               'role' =>$user->role
              
              );
            return response()->json([
            'status'=>true,
            'token' =>$token,
            'token_type' => 'bearer',
            'expires_in' => env('SESSION_TOKEN_EXPIRY'),
            'user' =>$user
        ], 200);
        }
        else{
            return response()->json(['status'=>false,'message' => 'Unauthorized'], 200);
        }
       
    }
    public function forgot_password(Request $request){
       
         $validator = Validator::make($request->all(), [
           
            'email' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
         $email=$request->post('email');
         $user_data=DB::table('users')->where('email',$email)->select('id','email')->first();
         
         if(!empty($user_data)){
            return response()->json(['status'=>true,'data' =>$user_data], 200);
         }else{
             return response()->json(['status'=>false,'message' => 'Email Not Found!'], 200);
         }
    }

    public function update_password(Request $request){
       
         $validator = Validator::make($request->all(), [
           
            'id' => 'required',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
        $id=$request->post('id');
        #check user exists
        if(DB::table('users')->where('id',$id)->count()>0)
        {               
         
         $user_data=User::where('id',$id)->update(['password'=>$request->input('password')]);         
          
         if($user_data){
            return response()->json(['status'=>true,'message' =>'Password Updated Successfully'], 200);
         }else{
             return response()->json(['status'=>false,'message' => 'Error on Update!'], 200);
         }
        }
        #user does not exists
        else{
            return response()->json(['status'=>false,'message' => 'User Id not Found'], 200);
        }
          
    }
}
