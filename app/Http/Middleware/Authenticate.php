<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Firebase\JWT\JWT;
use Exception;
use Firebase\JWT\ExpiredException;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */

    protected function create_token($id, $expiry)
    {
        $total_time = $expiry;
        $payload = [

            'iss' => "lumen-jwt", // Issuer of the token
            'userId' => $id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() +  $total_time // Expiration time
        ];


        return JWT::encode($payload, env('JWT_SECRET'));
    }
    public function decode_token($token)
    {

        $tokenParts = explode(".", $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);
        return $jwtPayload;
    }

    public function verify_token($token)
    {

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);

            return 1;
        } catch (ExpiredException $e) {
            return 2;
        } catch (Exception $e) {
            return 3;
        }
    }
    public function handle($request, Closure $next, $guard = null)
    {

        // if ($this->auth->guard($guard)->guest()) {
        //     return response()->json(['status' => 'false', 'message' => 'Token Required'], 401);
        // }

        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token Required'], 401);
        }
        $decode = $this->decode_token($token);
      
        $request['user_id'] = $decode->user_id;
        $verify_token = $this->verify_token($token);

        if ($verify_token == 3) {
            return response()->json(['status' => false, 'message' => 'Invalid Token'], 401);
        } else if ($verify_token == 2) {
            return response()->json([
                'status' => 'false',
                'message' => 'Provided token is expired.',
            ], 400);
        } else {
           
            return $next($request);
        }
    }
}
