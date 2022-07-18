<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
use Illuminate\Http\Request;
use DB;
use Validator;

class LearningController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>
        [
            'getLearningStandardMaths','get_subjects'
        ],
        ]);
    }
public function get_subjects(Request $request)
{
        $country_code=$request->country_code;
        $data=DB::table('subjects as s')->where('s.country_code',$country_code)
                ->join('subcategory as sc','sc.subject_id','=','s.id')
                ->distinct('s.subject_name')
                ->select('s.id','s.subject_name')->get();
        return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);
}
   
   public function getrecommendations(Request $request)    
    {
        #check student only logged or not
        if($request['role']==5){
             
            try {
                     $get_existing_attnd_question=DB::table('test_history')
                                         ->whereRaw('correct_answer = student_answer')
                                        ->where('student_id',$request['user_id'])
                                        ->pluck('question_id');

                    $res=json_decode(json_encode($get_existing_attnd_question,true));
                    $get_existing_attnd_question=$res;
                    if($get_existing_attnd_question){
                        $data = DB::table('questions')->where('country_code',$request->post('country_code'))
                                ->whereNotIn('id',$get_existing_attnd_question)
                                ->select('standard_id','question_text','question_image','subcategory_id as id','flag','id as question_id')
                                ->inRandomOrder()
                                ->limit(2)
                                ->get();
                        return response()->json(['status' => true, 'data' => $data], 200);                    
                }else{
                        $data = DB::table('questions')->where('country_code',$request->post('country_code'))
                                
                                ->select('standard_id','question_text','question_image','flag','subcategory_id as id','id as question_id')
                                ->inRandomOrder()
                                ->limit(2)
                                ->get();
                            return response()->json(['status' => true, 'data' => $data], 200);
                }
               
           
            
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
        }
        else{
            return response()->json(['status' => false, 'message' =>'unAuthorized'], 200);
        }
    }

    public function getLearningStandardMaths(Request $request){
        $result=[];$term='';
        if($request->has('subject_name') && $request->subject_name!==''){
             $term=strtolower($request->subject_name);
        }else{
            $term='maths';
        }
     
        $get_subject_id=DB::table('subjects')
                        ->where('country_code',$request->post('country_code'))
                        ->whereRaw('lower(subject_name) like (?)',["%{$term}%"])
                        ->pluck('id');
       
        if($get_subject_id!==0)
        {
            $standarData=DB::table('standards')->where('country_code',$request->post('country_code'))
                      
                        ->select('standard_name','id')
                        ->get();
       
                if($standarData)
                {
                    foreach($standarData as $standard){
                        $subtopicsData=DB::table('subcategory')->where('country_code',$request->post('country_code'))
                                        ->where('standard_id',$standard->id)
                                        ->select('id','name','standard_id')
                                        ->where('subject_id',$get_subject_id)
                                        ->inRandomOrder()
                                        ->limit(5)
                                        ->get();

                        if(count($subtopicsData)>0){
                            $result[]=array('standard_name'=>$standard->standard_name,'id'=>$standard->id,'topics'=>$subtopicsData);
                        }
                    }
                     return response()->json([
                            'status' => true,
                            'data' =>$result
                        ], 200);
                }
        }#Maths Sub Not Found
        else{
             return response()->json([
                'status' => false,
                'data' =>[]
            ], 200);
        }
        
       
         
    }
    public function learning_awards(Request $request){
        $student_id=$request['user_id'];
        $final_result=[];
        $award_data=DB::table('scores')->where('student_id',$student_id)->get();
        $final_result['total_medals']=DB::table('scores')->where('score',100)->where('student_id',$student_id)->count();
        $final_result['master_in']=DB::table('scores')->where('score',100)->where('student_id',$student_id)->count();  
        $final_result['next_step']=DB::table('scores')->where('student_id',$student_id)->whereBetween('score',[1,99])->count();
        $final_result['total_question']=DB::table('test_history')->where('student_id',$student_id)->count();
        $total_time=[];$total=0;
        if($award_data){
            
            foreach ($award_data as $key => $value) {
              
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
                   
                    $final_result['time']=$formatted;
        
            return response()->json([
                    'status' => true,
                    'data' =>$final_result
                ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'data' =>$final_result
            ], 200);
        }
        
    }
    
   

}