<?php

namespace App\Http\Controllers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use Validator;
use Illuminate\Http\Request;
use DB;

class SchoolController extends Controller
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

     public function getAllschools()
    {
        $final_data=[];
        try {
            $data = DB::table('users')->where('role',4)->orderBy('id','desc')->get();
           
            if($data){
                 $current_datetime=date('Y-m-d');
                foreach($data as $value){
                    $expiry='';$status='';
                    $type=$value->subscription_type;
                    $purchased_date=$value->purchased_datetime;
                      if($type=='monthly'){
                             $date =explode(" ",$purchased_date)[0];
                            $newdate =date("Y-m-d", strtotime ( '+1 month' , strtotime ( $date ) )) ;
                           
                            #check plan expired or not 
                            $diff= strtotime($newdate)-strtotime($current_datetime);          
                                if($diff >0){
                                   $status='Active';
                                   $expiry=$newdate;
                                }#plan expired
                                else{
                                    $status='Expired';
                                    $expiry=$newdate;
                                }
                        }#plan type Annual
                        else{
                            $date =explode(" ",$purchased_date)[0];
                            $newdate = date("Y-m-d", strtotime ( '+12 month' , strtotime ( $date ) )) ;
                            $diff= strtotime($newdate)-strtotime($current_datetime);    
                        
                            if ($diff>0){
                                $status='Active';
                                $expiry=$newdate;
                            }#plan expired
                            else{
                                $status='Expired';
                                $expiry=$newdate;
                            }
                        }                       
                    $final_data[]=array('name'=>$value->name,'email'=>$value->email,'subscription_type'=>$value->subscription_type,
                    'purchased_datetime'=>$value->purchased_datetime,'status'=>$status,'expiry'=>$expiry,'no_of_children'=>$value->no_of_children);
                }
            }

            return response()->json(['status' => true, 'data' => $final_data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }
}
