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
            'except'=>['login','register','admin_login'],
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
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'type' => 'required',
            'package_for' => 'required',
            'no_of_children' => 'required',
        ]);
       

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }

        try {
            $plainPassword = $request->input('password');
            $role='';
             if($request->input('package_for')=='school'){
                    $role=4;
                }else{
                    $role=3;
                }
            DB::table('users')->insert([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($plainPassword),
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
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['status'=>false,'message' => 'Unauthorized'], 200);
        }
        $purchased_date='';
        #if student get parent purchase date
        if(Auth::user()->role==5){
            $getparent_id=DB::table('users')->where('id',Auth::user()->parent_id)
                            ->select('purchased_datetime')->first();
                            $purchased_date=$getparent_id->purchased_datetime;
        }else{
            $purchased_date=Auth::user()->purchased_datetime;
        }
        
        $current_datetime=date('Y-m-d');
        $type=Auth::user()->subscription_type;
        if($type=='monthly'){
           $date =explode(" ",$purchased_date)[0];
            $newdate =date("Y-m-d", strtotime ( '+1 month' , strtotime ( $date ) )) ;
            #check plan expired or not 
             $diff= strtotime($newdate)-strtotime($current_datetime);          
            if($diff >0){
                
                 $token = $this->create_token(Auth::user()->role,Auth::user()->id, env('SESSION_TOKEN_EXPIRY'));
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
                 $token = $this->create_token(Auth::user()->role,Auth::user()->id, env('SESSION_TOKEN_EXPIRY'));
               
                return $this->respondWithToken($token);
            }#plan expired
            else{
                return response()->json(['status'=>false,'message' => 'Plan Expired'], 200);
            }
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
        }
        
        $token = $this->create_token(Auth::user()->id, env('SESSION_TOKEN_EXPIRY'));
        return $this->respondWithToken($token);
    }
}
