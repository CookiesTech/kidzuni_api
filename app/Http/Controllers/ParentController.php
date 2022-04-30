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

     public function getAllParents()
    {
        try {
            $data = DB::table('users')->where('role',3)->orderBy('id','desc')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }
    public function add_kids(Request $request){
     $no_of_children=DB::table('users')->where('id',$request['user_id'])->select('no_of_children as child','country_code')->first();
     $child_count=$no_of_children->child;
  
         try {
                if (count($request->input('data')) > 0) {
                    foreach ($request->input('data') as $key => $value) 
                    {
                            if($value['email']!='' && $value['password'] && $value['name'])
                            {
                            $added_count=DB::table('users')->where('parent_id',$request['user_id'])->count(); 
                            
                            #check childcount
                            if($child_count >= $added_count)
                            {
                                    if(DB::table('users')->where('email',$value['email'])->count()==0)
                                    {
                                            $plainPassword = $value['password'];
                                            DB::table('users')->insert([
                                            'name'=>$value['name'],
                                            'email'=>$value['email'],
                                            'password'=>app('hash')->make($plainPassword),
                                            'parent_id'=>$request['user_id'],
                                            'country_code'=>$no_of_children->country_code,
                                            'role'=>5                            
                                            ]);
                                        }
                                        #email exists
                                        else{
                                            return response()->json(['status' => false, 'message' =>'email exists'], 200);
                                        }
                                }
                                #child countoverlimit
                                else{
                                    return response()->json(['status' => false, 'message' =>'Already kids added for you package Limit'], 200);
                                }

                            }#input valuce chack if end
                            else{
                                return response()->json(['status' => false, 'message' =>'Fill all Kidz Info'], 200);
                                }
                     
                    }#foreach end
                     $no_of_children=DB::table('users')->where('parent_id',$request['user_id'])->count();
                     
                     return response()->json(['status' => true, 'message' =>'Kidz Added Successfully','filled_count'=>$no_of_children], 200);

                }#no data if end
                else{
                    return response()->json(['status' => false, 'message' =>'kidz info empty'], 200);
                }
        }
         catch (\Exception $e) {
             print_r($e);exit;
            //return error message
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }
}
