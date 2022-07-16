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
            'except'=>['login','register','admin_login','forgot_password','update_password'],
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
            DB::table('users')->insert([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' =>md5($request->input('password')),
                'phone_no'=>$request->input('phone_no'),
                'no_of_children'=>$request->input('no_of_children'),
                'subscription_type'=>$request->input('type'),
                'package_for'=>$request->input('package_for'),
                'country_code'=>$request->input('country_code'),
                'price'=>$request->post('price'),
                 'role'=>$role,                
                'purchased_datetime'=>date('Y-m-d H:i:s'),
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
                ->wherePassword(md5($request->password))
                ->first();
        }else{
           $user = DB::table('users')->whereEmail($request->username)
                ->wherePassword(md5($request->password))
                ->first();

           
        }
        
        if($user)
        {          
            $purchased_date='';
            #if student get parent purchase date
            if($user->role==5){
                $getparent_id=DB::table('users')->where('id',$user->parent_id)
                                ->select('purchased_datetime')->first();
                                $purchased_date=$getparent_id->purchased_datetime;
            }else{
                $purchased_date=$user->purchased_datetime;
            }
            
            $current_datetime=date('Y-m-d');
            $type=$user->subscription_type;
            if($type=='monthly'){
                 $date =explode(" ",$purchased_date)[0];
                $newdate =date("Y-m-d", strtotime ( '+1 month' , strtotime ( $date ) )) ;
                #check plan expired or not 
                $diff= strtotime($newdate)-strtotime($current_datetime);  
                    
                if($diff >0){
                    
                    $token = $this->create_token($user->role,$user->id, env('SESSION_TOKEN_EXPIRY'));
                    return $this->respondWithToken($token);
                }#plan expired
                else{
                    return response()->json(['status'=>false,'message' => 'Plan Expired'], 200);
                }
            }#plan type Annual
            else{
                $date =explode(" ",$purchased_date)[0];
                $newdate = date("Y-m-d", strtotime ( '+12 month' , strtotime ( $date ) )) ;
                $diff= strtotime($newdate)-strtotime($current_datetime);    
                
                if ($diff>0){
                        $token = $this->create_token($user->role,$user->id, env('SESSION_TOKEN_EXPIRY'));
                    
                        return $this->respondWithToken($token,$user);
                    }#plan expired
                    else{
                        return response()->json(['status'=>false,'message' => 'Plan Expired'], 200);
                    }
            }

            }else{

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
        
        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['status'=>false,'message' => 'Unauthorized'], 200);
        }#to only admin can login
        else if(Auth::user()->role==1){

            $token = $this->create_token(Auth::user()->role,Auth::user()->id, env('SESSION_TOKEN_EXPIRY'));
            return $this->respondWithToken1($token);
        }else{
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
         
          $plainPassword = $request->input('password');
          
         $user_data=User::where('id',$id)->update(['password'=>app('hash')->make($plainPassword)]);         
          
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
