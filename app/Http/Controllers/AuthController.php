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
            'except'=>['login','register'],
        ]);
    }
    protected function create_token($id, $expiry)
    {
        $total_time = $expiry;
        $payload = [

            'iss' => "lumen-jwt", // Issuer of the token
            'user_id' => $id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() +  $total_time // Expiration time
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
            'no_of_chilren' => 'required',
        ]);
       

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }

        try {
            $plainPassword = $request->input('password');
            DB::table('users')->insert([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($plainPassword),
                'phone_no'=>$request->input('phone_no'),
                'no_of_children'=>$request->input('no_of_children'),
                'subscription_type'=>$request->input('type'),
                'package_for'=>$request->input('package_for'),
                'role'=>3,
                'purchased_datetime'=>date('d-m-Y H:i:s'),
                'address'=>$request->input('address'),
                'school_name'=>$request->input('school_name'),

            ]);


            //return successful response
            return response()->json(['status' => true, 'message' => 'User Registration Successfully'], 200);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['status' => false, 'message' => 'User Registration Failed!'], 409);
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
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $token = $this->create_token(Auth::user()->id, env('SESSION_TOKEN_EXPIRY'));
        return $this->respondWithToken($token);
    }
}
