<?php

namespace App\Http\Controllers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use Validator;
use Illuminate\Http\Request;
use DB;

class ParentController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add_kids(Request $request){
     
         try {
                if (count($request->input('data')) > 0) {
                    foreach ($request->input('data') as $key => $value) {
                        
                        if(DB::table('users')->where('email',$value['email'])->count()==0){
                             $plainPassword = $value['password'];
                            DB::table('users')->insert([
                            'name'=>$value['name'],
                            'email'=>$value['email'],
                            'password'=>app('hash')->make($plainPassword),
                            'parent_id'=>$request['user_id'],
                            'role'=>5                            
                            ]);
                        }
                        #email exists
                        else{
                             return response()->json(['status' => false, 'message' =>'email exists'], 200);
                        }
                    }
                     return response()->json(['status' => true, 'message' =>'Kidz Added Successfully'], 200);

                }#no data
                else{
                    return response()->json(['status' => false, 'message' =>'kidz info empty'], 200);
                }
        }
         catch (\Exception $e) {
            //return error message
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }
}
