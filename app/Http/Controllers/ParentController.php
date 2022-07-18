<?php

namespace App\Http\Controllers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use Validator;
use Carbon\Carbon;
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
        $final_data=[];
        try {
            $data = DB::table('users')->where('role',3)->orderBy('id','desc')->get();
           
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
    public function add_kids(Request $request){   
  
         try {
                if (count($request->input('data')) > 0) {
                    foreach ($request->input('data') as $key => $value) 
                    {
                            if($value['username']!='' && $value['password'] && $value['name'])
                            {
                            $added_count=DB::table('users')->where('parent_id',$request['user_id'])->count(); 
                             $no_of_children=DB::table('users')->where('id',$request['user_id'])->select('no_of_children as child','country_code','expiry_date')->first();
                             $child_count=$no_of_children->child;
                            #check childcount
                            if($child_count > $added_count)
                            {
                                    if(DB::table('users')->where('username',$value['username'])->count()==0)
                                    {
                                            
                                            DB::table('users')->insert([
                                            'username'=>$value['username'],
                                            'name'=>$value['name'],
                                            'password'=>$value['password'],
                                            'parent_id'=>$request['user_id'],
                                            'country_code'=>$no_of_children->country_code,
                                            'expiry_date'=>$no_of_children->expiry_date,
                                            'role'=>5                            
                                            ]);
                                        }
                                        #name exists
                                        else{
                                            return response()->json(['status' => false, 'message' =>'name exists'], 200);
                                        }
                                }
                                #child countoverlimit
                                else{
                                    return response()->json(['status' => false, 'message' =>'Already kids added for you package Limit'], 200);
                                }

                            }#input value check if end
                            else{
                                return response()->json(['status' => false, 'message' =>'Fill all Kidz Info'], 200);
                                }
                     
                    }#foreach end
                     $no_of_children=DB::table('users')->where('parent_id',$request['user_id'])->count();
                      $kidz_data=DB::table('users')->where('parent_id',$request['user_id'])->select('id','name','username','password')->get();
                     
                     return response()->json(['status' => true, 'message' =>'Kidz Added Successfully','filled_count'=>$no_of_children,'data'=>$kidz_data], 200);

                }#no data if end
                else{
                    return response()->json(['status' => false, 'message' =>'kidz info empty'], 200);
                }
        }
         catch (\Exception $e) {
           
            //return error message
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }
    public function get_kidz_details(Request $request){
       
        $kidz_data=DB::table('users')->where('parent_id',$request['user_id'])->select('id','name','username','password')->get();
      
        return response()->json(['status' => true,'data'=>$kidz_data], 200);
    }
    public function getStudentsList(Request $request){
        $parent_id=$request['user_id'];
        $kidData=DB::table('users')->where('parent_id',$parent_id)->select('id','name')->get();
        if($kidData){
            return response()->json(['status'=>true,'data'=>$kidData]);
        }else{
            return response()->json(['status'=>false,'data'=>[]]);
        }

    }
public function getParentProgress(Request $request){
       //->whereBetween('score',[80,99])
        $subject_id='';$standard_id='';$student_id='';
         if(empty($request->student_id)){
            $get_stud_id=DB::table('users')->where('parent_id',$request['user_id'])->limit(1)->pluck('id');
            if(count($get_stud_id)>0){
                $student_id=$get_stud_id[0];
            }else{
                $student_id=$request->student_id;
            }
         }
     if($request->post('standard_id')=='All'){  
       
        $standard_id=DB::table('standards')->where('country_code',$request->post('country_code'))->pluck('id');
       
        }else{
             
            $standard_id=[$request->post('standard_id')];
        }
        if($request->post('subject_id')=='All'){
            $subject_id=DB::table('subjects')->where('country_code',$request->post('country_code'))->pluck('id');
           
        }else{
            $subject_id=[$request->post('subject_id')];
        }
        $date_range=$request->date_range;
        $country_code=$request->country_code;
        $progressData='';
       
        if($date_range=='month'){
            $progressData=DB::table('scores')->where('student_id',$student_id)->whereMonth('created_at',date('m'))
                        ->whereIn('standard_id',$standard_id)->whereIn('subject_id',$subject_id)->get();
        }else if($date_range=='last_week'){
            $progressData=DB::table('scores')->where('student_id',$student_id)->whereBetween('created_at',[Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                        ->whereIn('standard_id',$standard_id)->whereIn('subject_id',$subject_id)->get();
        }else{
            $today=explode(' ',Carbon::now())[0];
              $progressData=DB::table('scores')->where('student_id',$student_id)->whereDate('created_at', $today) 
                       ->whereIn('standard_id',$standard_id)->whereIn('subject_id',$subject_id)->get();
        }

     
        $final_result = array();
        if($progressData){
          
            foreach ($progressData as $key => $progress) {
                $finalData=DB::table('subcategory as sub')
                                ->where('sub.id',$progress->subcategory_id)
                                ->where('scores.student_id',$student_id)
                                ->join('scores','scores.subcategory_id','=','sub.id')
                                ->select('sub.name','scores.subcategory_id','scores.score','scores.time_spent')
                                ->get();
                foreach($finalData as $final){                                    
                    $count=DB::table('test_history')->where('subcategory_id',$final->subcategory_id)->where('student_id',$student_id)->count();
                    $final->total_attn=$count;
                    $final_result[] = $final;
                }
                                

            }
        }

         return response()->json([
                'status' => true,
                'data' =>$final_result
            ], 200);
    }
    public function getParentAnalyticsusage (Request $request)
    {         
         $subject_id='';$standard_id='';$student_id='';$data=[];
         if(empty($request->student_id)){
            $get_stud_id=DB::table('users')->where('parent_id',$request['user_id'])->limit(1)->pluck('id');
            if(count($get_stud_id)>0){
                $student_id=$get_stud_id[0];
            }else{
                $student_id=$request->student_id;
            }
         }
        if($request->post('standard_id')=='All'){  
       
        
        $standard_id=DB::table('standards')->where('country_code',$request->post('country_code'))->pluck('id');
       
        }else{
           
            $standard_id=[$request->post('standard_id')];
        }
        if($request->post('subject_id')=='All'){
            $subject_id=DB::table('subjects')->where('country_code',$request->post('country_code'))->pluck('id');
           
        }else{
            $subject_id=[$request->post('subject_id')];
        }
       $inputDaterange=$request->post('date_range');
      
       if($inputDaterange=='month')
       {           
           
            $data['correctAnswer_sum']=DB::table('test_history')
                                ->where('student_id',$student_id)
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereMonth('created_at',date('m'))
                                ->whereRaw('correct_answer = student_answer')
                                ->count();
           
            $data['wrongAnswer_sum']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereMonth('created_at',date('m'))
                                ->where('student_id',$student_id)
                                ->whereRaw('correct_answer != student_answer')
                                ->count();
            $data['topicsCount']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereMonth('created_at',date('m'))
                                ->where('student_id',$student_id)
                                ->distinct('subcategory_id')
                                ->count();
            $timeData=DB::table('scores')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereMonth('created_at',date('m'))
                                ->where('student_id',$student_id)
                                ->select('time_spent')->get();

                $total=0;
                if($timeData){
                    foreach ($timeData as $key => $value) {
                        
                        $temp = explode(":", $value->time_spent);     
                            // Convert the hours into seconds
                            // and add to total
                            $total+= (int) $temp[0] * 3600;                                    
                            // Convert the minutes to seconds
                            // and add to total
                            $total+= (int) $temp[1] * 60;                                    
                            // Add the seconds to total
                            $total+= (int) $temp[2];
                    }
                    $formatted = sprintf('%02d:%02d:%02d',
                                ($total / 3600),
                                ($total / 60 % 60),
                                $total % 60);

                    $data['total_time']=$formatted;
                    }else
                    {
                        $data['total_time']=0;
                    }
       }else if($inputDaterange=='last_week')
       {
             $data['correctAnswer_sum']=DB::table('test_history')
                                ->where('student_id',$student_id)
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereBetween('created_at',[Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                                ->whereRaw('correct_answer = student_answer')
                                ->count();
            $data['wrongAnswer_sum']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereBetween('created_at',[Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                                ->where('student_id',$student_id)
                                ->whereRaw('correct_answer != student_answer')
                                ->count();
            $data['topicsCount']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereBetween('created_at',[Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                                ->where('student_id',$student_id)
                                ->distinct('subcategory_id')
                                ->count();
            $timeData=DB::table('scores')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereBetween('created_at',[Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                                ->where('student_id',$student_id)
                                ->select('time_spent')->get();

                $total=0;
                if($timeData){
                    foreach ($timeData as $key => $value) {
                        
                        $temp = explode(":", $value->time_spent);     
                            // Convert the hours into seconds
                            // and add to total
                            $total+= (int) $temp[0] * 3600;                                    
                            // Convert the minutes to seconds
                            // and add to total
                            $total+= (int) $temp[1] * 60;                                    
                            // Add the seconds to total
                            $total+= (int) $temp[2];
                    }
                    $formatted = sprintf('%02d:%02d:%02d',
                                ($total / 3600),
                                ($total / 60 % 60),
                                $total % 60);

                    $data['total_time']=$formatted;
                    }else
                    {
                        $data['total_time']=0;
                    }
       }
       # for yesterday
       else if ($inputDaterange=='today')           
       {
           $yesderday=explode(' ',Carbon::now())[0];
            $data['correctAnswer_sum']=DB::table('test_history')
                                ->where('student_id',$student_id)
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $yesderday)
                                ->whereRaw('correct_answer = student_answer')
                                ->count();
            $data['wrongAnswer_sum']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $yesderday)
                                ->where('student_id',$student_id)
                                ->whereRaw('correct_answer != student_answer')
                                ->count();
            $data['topicsCount']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $yesderday)
                                ->where('student_id',$student_id)
                                ->distinct('subcategory_id')
                                ->count();
            $timeData=DB::table('scores')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $yesderday)
                                ->where('student_id',$student_id)
                                ->select('time_spent')->get();

                $total=0;
                if($timeData){
                    foreach ($timeData as $key => $value) {
                        
                        $temp = explode(":", $value->time_spent);     
                            // Convert the hours into seconds
                            // and add to total
                            $total+= (int) $temp[0] * 3600;                                    
                            // Convert the minutes to seconds
                            // and add to total
                            $total+= (int) $temp[1] * 60;                                    
                            // Add the seconds to total
                            $total+= (int) $temp[2];
                    }
                    $formatted = sprintf('%02d:%02d:%02d',
                                ($total / 3600),
                                ($total / 60 % 60),
                                $total % 60);

                    $data['total_time']=$formatted;
                    }else
                    {
                        $data['total_time']=0;
                    }
       }       
      
        return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);
                   
    }


    public function kid_profile_update(Request $request){
        $parent_id=$request['user_id'];
         $validator = Validator::make($request->all(), [
           
            'id' => 'required',
            'password' => 'required|string',
            'name' => 'required|string',
            'username' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
        $id=$request->post('id');
        #check user exists
        if(DB::table('users')->where('id',$id)->count()>0)
        {               
         #check exists username for other user
         if(DB::table('users')->where('username',$request->username)->where('id','!=',$id)->count()==0)
         {
           
            $user_data=User::where('id',$id)->update(['password'=>$request->password,
                            'name'=>$request->name,'username'=>$request->username
                        ]);         
            
            if($user_data){
                $kidz_data=DB::table('users')->where('parent_id',$parent_id)->select('id','name','email','role','username','password')->get();
                return response()->json(['status'=>true,'message' =>'Profile Updated Successfully','kidz_data'=>$kidz_data], 200);
            }else{
                return response()->json(['status'=>false,'message' => 'Error on Update!'], 200);
            }
         }
         #same username already exists
         else
         {
            return response()->json(['status'=>false,'message' => 'Username already exists'], 200);
         }

            
        }
        #user does not exists
        else{
            return response()->json(['status'=>false,'message' => 'User Id not Found'], 200);
        }
          
    }
}
