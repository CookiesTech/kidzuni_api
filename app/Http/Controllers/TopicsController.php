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
       
        $res['Topics'] = array();       
         $validator = Validator::make($request->all(), [
            'standard_id' => 'required',            
            'subject_id' => 'required'
        ]);
        $student_id=$request->post('student_id');
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
      
            $data = DB::table('maincategory as m')            
                    ->where('m.country_code',$request->post('country_code'))   
                    ->where('m.standard_id',$request->post('standard_id'))      
                    ->where('m.subject_id',$request->post('subject_id'))        
                    ->select('m.id','m.name as name')->get();
            $subTopics=[];        
            if(count($data)>0)
            {
                $score=0;
                foreach($data as $maintopics)
                {
                    $subTopics=DB::table('subcategory as sc')
                                ->where('sc.mc_id',$maintopics->id)
                                ->where('sc.standard_id',$request->post('standard_id'))
                                ->where('sc.country_code',$request->post('country_code'))
                                ->groupBy('sc.name','sc.id')
                                ->select('sc.id','sc.name')
                                ->get();
                    if(count($subTopics)>0){
                        $res['Topics'][]=array('main_topic'=>$maintopics->name,'sub_topics'=>$subTopics);
                    }
                    
                               
                } #maintopic lop end
               foreach ($res['Topics'] as $key => $value) {
                  foreach ($value['sub_topics'] as $key => $subtopics) {
                      if($student_id){
                          $getscore=DB::table('scores')->where('subcategory_id',$subtopics->id)->where('student_id',$student_id)->select('score')->get();
                            if(count($getscore)>0){
                                $subtopics->score=$getscore[0]->score;
                            }#user Looged in but no test attent this sub topic
                            else{
                                $subtopics->score=0;
                            }
                        }#user not logged in
                        else{
                            $subtopics->score=0;
                      }

                  }
               }


            }
            else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
            return response()->json(['status' => true, 'data' =>$res], 200);
       
    }

}
