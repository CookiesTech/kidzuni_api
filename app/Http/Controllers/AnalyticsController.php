<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
use Illuminate\Http\Request;
use DB;
use Validator;
use Carbon\Carbon;

class AnalyticsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAnalysticsUsage(Request $request)
    {  
         $student_id=$request['user_id'];
        $data=[];
     $subject_id='';$standard_id='';
     if($request->post('standard_id')=='All'){         
         $standard_id=DB::table('standards')->where('country_code',$request->post('country_code'))->pluck('id');
       
        }else{
            $standard_id=[$request->post('standard_id')];
        }
        if($request->post('subject_id')=='All'){
            $subject_id=DB::table('subjects')->where('country_code',$request->post('country_code'))->limit(1)->pluck('id');
           
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
       # for today
       else if ($inputDaterange=='today')           
       {
           $today=explode(' ',Carbon::now())[0];
            $data['correctAnswer_sum']=DB::table('test_history')
                                ->where('student_id',$student_id)
                               ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $today)
                                ->whereRaw('correct_answer = student_answer')
                                ->count();
            $data['wrongAnswer_sum']=DB::table('test_history')
                                ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $today)
                                ->where('student_id',$student_id)
                                ->whereRaw('correct_answer != student_answer')
                                ->count();
            $data['topicsCount']=DB::table('test_history')
                               ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $today)
                                ->where('student_id',$student_id)
                                ->distinct('subcategory_id')
                                ->count();
            $timeData=DB::table('scores')
                               ->whereIn('standard_id',$standard_id)
                                ->whereIn('subject_id',$subject_id)
                                ->whereDate('created_at', $today)
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

    public function analysticsProgress(Request $request){
        $student_id=$request['user_id'];//->whereBetween('score',[80,99])
        $subject_id='';$standard_id='';
     if($request->post('standard_id')=='All'){         
         $standard_id=DB::table('standards')->where('country_code',$request->post('country_code'))->pluck('id');
       
        }else{
            $standard_id=[$request->post('standard_id')];
        }
        if($request->post('subject_id')=='All'){
            $subject_id=DB::table('subjects')->where('country_code',$request->post('country_code'))->limit(1)->pluck('id');
           
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

    public function analyticsQuestionLog(Request $request){
        $student_id=$request['user_id'];
        $subcategory_id=$request->post('subcategory_id');
        $data=DB::table('test_history as th')->where('th.student_id',$student_id)
              ->where('th.subcategory_id',$subcategory_id)
              ->join('questions as q','q.id','=','th.question_id')
              ->select('th.correct_answer','th.student_answer','q.question_text as question','q.option1','q.option2','q.flag')
              ->get();
              return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);

    }

    public function analytics_standard(Request $request){
        $country_code=$request->post('country_code');
         $final_data=DB::table('standards')->where('country_code',$country_code)->select('id','standard_name')->get();
            
        return response()->json([
                'status' => true,
                'data' =>$final_data
            ], 200);
    }

    public function analytics_subjects(Request $request){
            $country_code=$request->post('country_code');
            $final_data=DB::table('subjects')->where('country_code',$country_code)
                       ->select('id','subject_name')
                        ->get();
                
            return response()->json([
                    'status' => true,
                    'data' =>$final_data
                ], 200);
        }


}