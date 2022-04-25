<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
use Illuminate\Http\Request;
use DB;
use Validator;

class AnalyticsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAnalytics(Request $request){       
      $student_id=$request['user_id'];
        $data['correctAnswer_sum']=DB::table('test_history')
                        ->where('student_id',$student_id)
                        ->whereRaw('correct_answer = student_answer')
                        ->count();
      $data['wrongAnswer_sum']=DB::table('test_history')
                        ->where('student_id',$student_id)
                        ->whereRaw('correct_answer != student_answer')
                        ->count();
      $data['topicsCount']=DB::table('test_history')
                        ->where('student_id',$student_id)
                        ->distinct('subcategory_id')
                        ->count();
     $timeData=DB::table('scores')
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
        }else{
            $data['total_time']=0;
        }

        return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);
                   
    }

}