<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class TopicsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',[
            'except'=>['getTopics'],
        ]);
    }


    public function getTopics(Request $request)
    {
        $categroies = array();
        $sub_categroies = array();
        $sub = array();
        $child = array();
        $res = array();
       
         $validator = Validator::make($request->all(), [
            'standard_id'           => 'required'
        ]);
        $student_id=$request->post('student_id')?$request->post('student_id'):'';
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
        try {
            $data = DB::table('maincategory as m')->where('m.standard_id',$request->post('standard_id'))->select('id','name')->get();
           
            if(count($data)>0){
                $i = 0;$score=0;
                foreach($data as $maintopics){
                     $categroies = array('main_topic' => $maintopics->name);
                     $sub_topics=DB::table('subcategory')->where('mc_id',$maintopics->id)->select('id','name')->get();
                    foreach ($sub_topics as $key => $value) {
                        #no login 
                        if($student_id==''){
                            $score=0;
                        } #user logged in
                        else{
                            $score=DB::table('scores')->where('subcategory_id',$value->id)->where('student_id',$student_id)->sum('score');
                        }                      
                        $value->score=$score;
                        $sub['sub_topics'][] = $value;
                    }
                    
                     $sub['sub_topics'] = $sub['sub_topics'];
                    $res['Topics'][$i] = array_merge($categroies, $sub);
                     $i++;
                }
            }else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
            return response()->json(['status' => true, 'data' =>$res], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

}
