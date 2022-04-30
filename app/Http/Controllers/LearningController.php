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
        $this->middleware('auth');
    }

   public function getrecommendations(Request $request)    
    {
        #check student only logged or not
        if($request['role']==5){
             
            try {
                     $get_existing_attnd_question=DB::table('test_history')
                                        ->where('student_id',$request['user_id'])
                                        ->pluck('question_id');

                    $res=json_decode(json_encode($get_existing_attnd_question,true));
                    $get_existing_attnd_question=$res;
                    if($get_existing_attnd_question){
                        $data = DB::table('questions')->where('country_code',$request->post('country_code'))
                                ->whereNotIn('id',$get_existing_attnd_question)
                                ->select('standard_id','question_text','question_image')
                                ->inRandomOrder()
                                ->limit(2)
                                ->get();
                        return response()->json(['status' => true, 'data' => $data], 200);                    
                }else{
                        $data = DB::table('questions')->where('country_code',$request->post('country_code'))
                                
                                ->select('standard_id','question_text','question_image')
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
        $result=[];
        $term='Maths';
        $get_subjectID=DB::table('subjects')
                        ->where('country_code',$request->post('country_code'))
                        ->whereRaw('lower(subject_name) like (?)',["%{$term}%"])
                        ->select('standard_id','id')
                        ->get();
       
        if(count($get_subjectID)>0)
        {
            $standarData=DB::table('standards')->where('country_code',$request->post('country_code'))
                        ->where('id',$get_subjectID[0]->standard_id)
                        ->select('standard_name','id')
                        ->get();
       
                if($standarData)
                {
                    foreach($standarData as $standard){
                        $subtopicsData=DB::table('subcategory')->where('country_code',$request->post('country_code'))
                                        ->where('standard_id',$standard->id)
                                        ->select('id','name','standard_id')
                                        ->inRandomOrder()
                                        ->limit(5)
                                        ->get();

                        $result[]=array('standard_name'=>$standard->standard_name,'id'=>$standard->id,'topics'=>$subtopicsData);
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
        $award_data=DB::table('scores')->where('score',100)->where('student_id',$student_id)->get();
        $final_result['total_medals']=count($award_data);
        $final_result['master_in']=count($award_data);  
        $final_result['next_step']=DB::table('scores')->where('student_id',$student_id)->whereBetween('score',[1,99])->count();
        $total_question=0; $total_time=[];$total=0;
        if($award_data){
            
            foreach ($award_data as $key => $value) {
                $total_question+=DB::table('test_history')->where('student_id',$student_id)->where('subcategory_id',$value->subcategory_id)->count();
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
                    $final_result['total_question']=$total_question;
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